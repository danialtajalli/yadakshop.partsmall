#!/usr/bin/env python3
"""Bound closed Partsmall binlogs for an explicitly standalone database."""

import argparse
import datetime
import fcntl
import json
import pathlib
import re
import subprocess
import sys


CONTAINER = "partsmall-prod-mysql-1"
PROJECT = "partsmall-prod"
VOLUME = "partsmall-prod-mysql"
DEFAULT_MAX_BYTES = 4 * 1024**3
LOG_NAME = re.compile(r"binlog\.[0-9]+")


def load_config(path):
    config = json.loads(pathlib.Path(path).read_text())
    if set(config) != {"max_bytes", "standalone_no_history"}:
        raise RuntimeError("Expected max_bytes and standalone_no_history configuration keys")
    limit = config["max_bytes"]
    if type(limit) is not int or not 64 * 1024**2 <= limit <= 1024**4:
        raise RuntimeError("max_bytes must be an integer between 64 MiB and 1 TiB")
    if type(config["standalone_no_history"]) is not bool:
        raise RuntimeError("standalone_no_history must be a boolean")
    return config


def parse_logs(output):
    logs = []
    previous = -1
    for line in output.splitlines():
        fields = line.split("\t")
        if len(fields) < 2 or not LOG_NAME.fullmatch(fields[0]):
            raise RuntimeError("Unexpected binary log inventory")
        sequence = int(fields[0].split(".")[1])
        if not fields[1].isdigit() or sequence <= previous:
            raise RuntimeError("Invalid binary log sizes or ordering")
        logs.append((fields[0], int(fields[1])))
        previous = sequence
    if not logs:
        raise RuntimeError("No binary log inventory available")
    return logs


def purge_plan(logs, limit):
    """Keep the newest suffix within the budget, always retaining the active log."""
    total = sum(size for _, size in logs)
    remaining = total
    first_kept = 0
    while remaining > limit and first_kept < len(logs) - 1:
        remaining -= logs[first_kept][1]
        first_kept += 1
    return {
        "max_bytes": limit,
        "before_bytes": total,
        "planned_kept_bytes": remaining,
        "cutoff": logs[first_kept][0] if first_kept else None,
        "purge_count": first_kept,
        "active_log": logs[-1][0],
        "active_exceeds_budget": logs[-1][1] > limit,
    }


class ProductionMySQL:
    def command(self, arguments):
        result = subprocess.run(
            ["/usr/bin/docker", "--host", "unix:///var/run/docker.sock"] + arguments,
            capture_output=True,
            text=True,
            timeout=10,
        )
        if result.returncode:
            # Avoid exposing credentials, query text, or database contents in the journal.
            raise RuntimeError("Production MySQL metadata/maintenance command failed")
        return result.stdout.strip()

    def identity(self):
        rows = json.loads(self.command(["inspect", CONTAINER]))
        if len(rows) != 1:
            raise RuntimeError("Unexpected container inventory")
        row = rows[0]
        if row.get("Name") != "/" + CONTAINER or not row["State"]["Running"]:
            raise RuntimeError("Production MySQL is not running")
        if row["Config"].get("Labels", {}).get("com.docker.compose.project") != PROJECT:
            raise RuntimeError("Container does not belong to the production project")
        mounts = [mount for mount in row["Mounts"] if mount["Destination"] == "/var/lib/mysql"]
        if len(mounts) != 1 or mounts[0].get("Type") != "volume" or mounts[0].get("Name") != VOLUME:
            raise RuntimeError("Unexpected production MySQL data volume")
        return row["Id"]

    def sql(self, query):
        return self.command([
            "exec", CONTAINER, "sh", "-c",
            'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot --batch --skip-column-names -e "$1"',
            "sh", query,
        ])

    def inventory(self):
        # Rotation between these two reads is harmless: refuse and retry next minute.
        logs = parse_logs(self.sql("SHOW BINARY LOGS;"))
        status = self.sql("SHOW MASTER STATUS;").splitlines()
        if len(status) != 1 or status[0].split("\t")[0] != logs[-1][0]:
            raise RuntimeError("Binary logs rotated during inspection; retry next check")
        return logs

    def require_standalone(self):
        checks = [
            'SELECT COUNT(*) FROM information_schema.processlist WHERE COMMAND LIKE "Binlog Dump%";',
            "SELECT COUNT(*) FROM performance_schema.replication_connection_configuration;",
            'SELECT COUNT(*) FROM mysql.user WHERE Repl_slave_priv="Y" AND User NOT IN '
            '("root","mysql.infoschema","mysql.session","mysql.sys");',
        ]
        if any(self.sql(query) != "0" for query in checks):
            raise RuntimeError("Replication metadata detected; refusing binlog purge")
        if self.sql("SHOW REPLICAS;"):
            raise RuntimeError("Registered replicas detected; refusing binlog purge")


def enforce_budget(database, config, enforce=False):
    if enforce and config["standalone_no_history"] is not True:
        raise RuntimeError("Purging requires explicit standalone/no-recovery-history authorization")
    identity = database.identity()
    plan = purge_plan(database.inventory(), config["max_bytes"])
    plan["mode"] = "enforce" if enforce else "check"
    if plan["before_bytes"] <= config["max_bytes"]:
        return dict(plan, status="within_budget", after_bytes=plan["before_bytes"])
    if not enforce:
        return dict(plan, status="would_purge" if plan["cutoff"] else "active_log_exceeds_budget")

    database.require_standalone()
    if database.identity() != identity:
        raise RuntimeError("MySQL container changed during inspection; refusing purge")
    # Recalculate after checks in case automatic expiry or writes changed the inventory.
    plan.update(purge_plan(database.inventory(), config["max_bytes"]))
    if plan["cutoff"]:
        cutoff = plan["cutoff"]
        if not LOG_NAME.fullmatch(cutoff):
            raise RuntimeError("Invalid purge cutoff")
        # MySQL removes only logs before the cutoff and maintains its own index.
        warnings = database.sql("PURGE BINARY LOGS TO '" + cutoff + "'; SHOW WARNINGS;")
        if warnings:
            raise RuntimeError("MySQL reported warnings during purge; inspect database state")
    after = database.inventory()
    total = sum(size for _, size in after)
    return dict(
        plan,
        after_bytes=total,
        status="purged" if total <= config["max_bytes"] else "still_above_budget",
    )


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--config", default="/etc/partsmall/binlog-cap.json")
    parser.add_argument("--enforce", action="store_true", help="Purge older closed logs if necessary")
    arguments = parser.parse_args()
    try:
        config = load_config(arguments.config)
        # The service creates this private runtime directory; flock prevents overlapping purges.
        with open("/run/partsmall-binlog-cap/lock", "a") as lock:
            try:
                fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
            except BlockingIOError:
                print(json.dumps({"status": "another_check_running"}))
                return 0
            result = enforce_budget(ProductionMySQL(), config, arguments.enforce)
        result["checked_at_utc"] = datetime.datetime.now(datetime.timezone.utc).isoformat()
        print(json.dumps(result, sort_keys=True))
        return 1 if result["status"] in {"still_above_budget", "active_log_exceeds_budget"} else 0
    except (OSError, ValueError, KeyError, TypeError, RuntimeError, subprocess.TimeoutExpired) as error:
        print(json.dumps({"status": "error", "message": str(error)}), file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
