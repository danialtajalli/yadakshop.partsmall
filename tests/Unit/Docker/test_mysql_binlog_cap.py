import copy
import importlib.util
import json
import pathlib
import tempfile
import unittest
from unittest import mock


ROOT = pathlib.Path(__file__).resolve().parents[3]
SPEC = importlib.util.spec_from_file_location("mysql_binlog_cap", ROOT / "docker/ops/mysql-binlog-cap.py")
cap = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(cap)


class FakeMySQL:
    def __init__(self, logs, replica=False, warnings="", refuse_purge=False):
        self.logs = list(logs)
        self.replica = replica
        self.warnings = warnings
        self.refuse_purge = refuse_purge
        self.mutations = []
        self.identities = ["same-container", "same-container"]

    def identity(self):
        return self.identities.pop(0)

    def inventory(self):
        return list(self.logs)

    def require_standalone(self):
        if self.replica:
            raise RuntimeError("Replication detected")

    def sql(self, query):
        self.mutations.append(query)
        if not self.refuse_purge:
            cutoff = query.split("'")[1]
            self.logs = self.logs[next(i for i, row in enumerate(self.logs) if row[0] == cutoff):]
        return self.warnings


class MySQLBinlogCapTest(unittest.TestCase):
    def config(self, limit=100):
        return {"max_bytes": limit, "standalone_no_history": True}

    def test_under_budget_does_not_mutate(self):
        db = FakeMySQL([("binlog.000001", 40), ("binlog.000002", 60)])
        result = cap.enforce_budget(db, self.config(), enforce=True)
        self.assertEqual("within_budget", result["status"])
        self.assertEqual([], db.mutations)

    def test_oldest_logs_are_purged_and_newest_logs_kept(self):
        db = FakeMySQL([("binlog.000001", 60), ("binlog.000002", 60), ("binlog.000003", 20)])
        result = cap.enforce_budget(db, self.config(), enforce=True)
        self.assertEqual("purged", result["status"])
        self.assertEqual(80, result["after_bytes"])
        self.assertEqual(["PURGE BINARY LOGS TO 'binlog.000002'; SHOW WARNINGS;"], db.mutations)
        self.assertEqual([("binlog.000002", 60), ("binlog.000003", 20)], db.logs)

    def test_dry_run_never_purges(self):
        db = FakeMySQL([("binlog.000001", 90), ("binlog.000002", 90)])
        result = cap.enforce_budget(db, self.config())
        self.assertEqual("would_purge", result["status"])
        self.assertEqual([], db.mutations)

    def test_explicit_standalone_authorization_is_required(self):
        db = FakeMySQL([("binlog.000001", 90), ("binlog.000002", 90)])
        with self.assertRaisesRegex(RuntimeError, "authorization"):
            cap.enforce_budget(db, {"max_bytes": 100, "standalone_no_history": False}, enforce=True)
        self.assertEqual([], db.mutations)

    def test_replication_blocks_purge(self):
        db = FakeMySQL([("binlog.000001", 90), ("binlog.000002", 90)], replica=True)
        with self.assertRaisesRegex(RuntimeError, "Replication"):
            cap.enforce_budget(db, self.config(), enforce=True)
        self.assertEqual([], db.mutations)

    def test_container_replacement_blocks_purge(self):
        db = FakeMySQL([("binlog.000001", 90), ("binlog.000002", 90)])
        db.identities = ["before", "after"]
        with self.assertRaisesRegex(RuntimeError, "container changed"):
            cap.enforce_budget(db, self.config(), enforce=True)
        self.assertEqual([], db.mutations)

    def test_active_log_is_kept_even_when_it_alone_exceeds_budget(self):
        db = FakeMySQL([("binlog.000001", 40), ("binlog.000002", 150)])
        result = cap.enforce_budget(db, self.config(), enforce=True)
        self.assertEqual([("binlog.000002", 150)], db.logs)
        self.assertEqual("still_above_budget", result["status"])
        self.assertTrue(result["active_exceeds_budget"])

    def test_only_active_log_cannot_be_deleted(self):
        db = FakeMySQL([("binlog.000001", 150)])
        result = cap.enforce_budget(db, self.config(), enforce=True)
        self.assertEqual("still_above_budget", result["status"])
        self.assertEqual([], db.mutations)

    def test_purge_warning_is_not_reported_as_success(self):
        db = FakeMySQL([("binlog.000001", 90), ("binlog.000002", 90)], warnings="Warning\t1\tBusy")
        with self.assertRaisesRegex(RuntimeError, "warnings"):
            cap.enforce_budget(db, self.config(), enforce=True)

    def test_unreached_budget_is_not_reported_as_success(self):
        db = FakeMySQL([("binlog.000001", 90), ("binlog.000002", 90)], refuse_purge=True)
        self.assertEqual("still_above_budget", cap.enforce_budget(db, self.config(), enforce=True)["status"])

    def test_inventory_rejects_unsafe_names_sizes_and_order(self):
        for output in ["../binlog.000001\t50", "binlog.000001';DELETE\t1", "binlog.000001\t-1", "", "binlog.000002\t1\nbinlog.000001\t1"]:
            with self.subTest(output=output), self.assertRaises(RuntimeError):
                cap.parse_logs(output)
        self.assertEqual([("binlog.000001", 50)], cap.parse_logs("binlog.000001\t50\tNo\n"))

    def test_live_replication_checks_fail_closed(self):
        database = cap.ProductionMySQL()
        for values in [["1"], ["0", "1"], ["0", "0", "1"], ["0", "0", "0", "replica"]]:
            with self.subTest(values=values), mock.patch.object(database, "sql", side_effect=values):
                with self.assertRaises(RuntimeError):
                    database.require_standalone()
        with mock.patch.object(database, "sql", side_effect=["0", "0", "0", ""]):
            database.require_standalone()

    def test_identity_rejects_wrong_project_volume_or_stopped_container(self):
        row = {"Id": "correct", "Name": "/partsmall-prod-mysql-1", "State": {"Running": True},
               "Config": {"Labels": {"com.docker.compose.project": "partsmall-prod"}},
               "Mounts": [{"Destination": "/var/lib/mysql", "Type": "volume", "Name": "partsmall-prod-mysql"}]}
        database = cap.ProductionMySQL()
        with mock.patch.object(database, "command", return_value=json.dumps([row])):
            self.assertEqual("correct", database.identity())
        for field in ["project", "volume", "state", "name"]:
            bad = copy.deepcopy(row)
            if field == "project":
                bad["Config"]["Labels"]["com.docker.compose.project"] = "other-site"
            elif field == "volume":
                bad["Mounts"][0]["Name"] = "other-site-mysql"
            elif field == "state":
                bad["State"]["Running"] = False
            else:
                bad["Name"] = "/another-container"
            with self.subTest(field=field), mock.patch.object(database, "command", return_value=json.dumps([bad])):
                with self.assertRaises(RuntimeError):
                    database.identity()

    def test_rotation_race_refuses_stale_inventory(self):
        database = cap.ProductionMySQL()
        with mock.patch.object(database, "sql", side_effect=["binlog.000001\t50", "binlog.000002\t4"]):
            with self.assertRaisesRegex(RuntimeError, "rotated"):
                database.inventory()

    def test_configuration_validation(self):
        with tempfile.TemporaryDirectory() as directory:
            target = pathlib.Path(directory) / "config.json"
            for config in [{"max_bytes": True, "standalone_no_history": True}, {"max_bytes": -1, "standalone_no_history": True},
                           {"max_bytes": cap.DEFAULT_MAX_BYTES, "standalone_no_history": "yes"}, {"max_bytes": cap.DEFAULT_MAX_BYTES}]:
                target.write_text(json.dumps(config))
                with self.subTest(config=config), self.assertRaises(RuntimeError):
                    cap.load_config(target)
            expected = {"max_bytes": cap.DEFAULT_MAX_BYTES, "standalone_no_history": False}
            target.write_text(json.dumps(expected))
            self.assertEqual(expected, cap.load_config(target))


if __name__ == "__main__":
    unittest.main()
