# Partsmall Production Binlog Policy

Scope: `partsmall-prod-mysql-1`, volume `partsmall-prod-mysql`, MySQL 8.0.46.
Host MySQL, rehearsal, other websites, cache drivers, and queues are not changed.

## Safety Gate for Manual Recovery-Preserving Maintenance

Before every manual policy apply/purge, take a fresh off-server SQL backup with source coordinates,
verify gzip integrity, restore in isolated MySQL, and compare business counts and
table checks. Preserve older backups. Review connected and disconnected replicas,
replication accounts/channels, GTIDs, and point-in-time recovery requirements.
The script checks local replication metadata, but cannot prove that an unknown
offline consumer does not exist. Do not confirm review when it is unresolved.

Before a purge, rotate through MySQL, choose a closed-log cutoff, archive every
earlier log off server, and verify gzip integrity plus each file's source SHA256.
Keep the dump's source log and subsequent required logs in the recovery archive.
MySQL's `PURGE BINARY LOGS TO` retains the named cutoff and later logs, including
the active log. Never use filesystem deletion, `RESET MASTER`, or volume cleanup.

## Standalone Total Storage Budget

On October 15, the owner explicitly selected a **4 GiB** total budget and
confirmed this database is standalone with no binary-log recovery history needed.
This authorizes discarding older closed binlogs to meet the budget, including
logs younger than the existing 24-hour expiration setting. The separate manual
recovery-preserving commands above retain their existing backup/archive gates.
This policy does not authorize deleting business data, queue jobs, failed jobs,
request-log rows, or any other site's logs.

`mysql-binlog-cap.py` enforces this owner-selected policy through MySQL's
`PURGE BINARY LOGS TO`, scoped to the production container, Compose project and
data volume. It always retains the active log and the newest closed logs that
fit within the total budget. It rechecks container identity and refuses purges
when replication accounts, channels, senders or registered replicas appear.
An unknown offline replica cannot be discovered, so explicit owner confirmation
is required before setting `standalone_no_history` to true. Disable this policy
before introducing replicas or a point-in-time recovery archive.

The host timer checks every minute. This is a retention budget, not a filesystem
quota: writes between checks and a single oversized active log can exceed it.
The service fails visibly if it cannot reach the target; it never deletes the
active log or blocks application writes to force compliance. The existing
24-hour automatic MySQL expiration and 64 MiB rotation target remain enabled.

Source files:

- `docker/ops/mysql-binlog-cap.py`
- `docker/ops/binlog-cap.json.example` (safe default: authorization is false)
- `docker/ops/partsmall-binlog-cap.service`
- `docker/ops/partsmall-binlog-cap.timer`
- `tests/Unit/Docker/test_mysql_binlog_cap.py`

Installed host paths are `/usr/local/lib/partsmall/mysql-binlog-cap.py`,
`/etc/partsmall/binlog-cap.json`, and matching units in `/etc/systemd/system/`.
The reviewed production configuration is:

```json
{"max_bytes": 4294967296, "standalone_no_history": true}
```

Inspect the service and its latest checks:

```bash
sudo systemctl status partsmall-binlog-cap.timer --no-pager
sudo journalctl -u partsmall-binlog-cap.service -n 10 --no-pager
```

The application images and MySQL do not need restarting to install this host
timer. Disable the controller with `systemctl disable --now
partsmall-binlog-cap.timer`; stopping the timer does not recover already purged
binlogs. The installed service uses a private lock to prevent overlapping runs.

Run the isolated policy tests with Python 3 on Linux/WSL:

```bash
python3 -m unittest discover -s tests/Unit/Docker -p test_mysql_binlog_cap.py -v
```

## Upload Without Deploying Redis

Preserve relative paths when uploading these local files to the active release
`/var/www/partsmall-releases/production-20261008/`:

- `compose.prod.yaml`
- `docker/init-production-env.php`
- `docker/ops/mysql-binlog-policy.sh`
- `docker/ops/BINLOG_POLICY.md`
- `docker/ops/REMEDIATION.md`

Keep `.env.production.example` and tests in Git. Never replace the server's
credential-bearing `.env` or `.env.production` with a template or local file.
If that file defines binlog overrides, synchronize only these nonsecret values:

```dotenv
MYSQL_BINLOG_EXPIRE_LOGS_SECONDS=86400
MYSQL_MAX_BINLOG_SIZE=67108864
MYSQL_BINLOG_TRANSACTION_COMPRESSION=ON
```

On the October 11 inspection, production `.env.production` defined none of these
keys: the new Compose defaults therefore suffice, without an environment edit.
Inspect first. Before any future edit, preserve `.env.production` in a protected backup
outside the release. Keep one definition per key; leave every other value intact.
An existing 2592000 value would override the new Compose default. The generator
only creates new environments and does not update the existing production file.

```bash
ssh partsmall-server
cd /var/www/partsmall-releases/production-20261008
sudo bash docker/ops/mysql-binlog-policy.sh check
```

No rebuild or restart is required when the live policy was already applied.
Do not run a full `docker compose up` in this step: this source also includes
Redis/cache/queue changes reserved for the separately reviewed transition.

## Live Maintenance

These flags attest completed checks, not instructions to skip them. Run only
after a fresh backup and verified restore, recovery review, and (for purge) a
complete verified off-server binlog archive. Substitute the reviewed cutoff.

```bash
sudo env BACKUP_VERIFIED=1 RESTORE_VERIFIED=1 RECOVERY_REVIEWED=1 \
  BINLOG_ARCHIVE_VERIFIED=1 \
  bash docker/ops/mysql-binlog-policy.sh purge binlog.NNNNNN

sudo env BACKUP_VERIFIED=1 RESTORE_VERIFIED=1 RECOVERY_REVIEWED=1 \
  MYSQL_BINLOG_EXPIRE_LOGS_SECONDS=86400 MYSQL_MAX_BINLOG_SIZE=67108864 \
  MYSQL_BINLOG_TRANSACTION_COMPRESSION=ON \
  bash docker/ops/mysql-binlog-policy.sh apply
```

Apply uses `SET PERSIST` for rotation size, compression, retention and auto-purge;
MySQL manages its own `mysqld-auto.cnf`. Do not edit that file manually. A shorter
retention can expire old logs at a subsequent rotation/startup, so complete
archival before applying it. Compression may not affect existing long-lived
sessions until they reconnect. Check actual compression counters, new connection
session variables, and binlog growth; do not restart workers without queue review.

Verify effective and persisted values, HTTP root/up/admin responses, container
health, business counts, and disk bytes/inodes. Measure binlog bytes per hour/day
and over a full retention cycle. A 24-hour policy is not a hard disk quota; alert
at 80%/90% and on rapid growth/time-to-full. Schedule daily off-server backups and
recovery-log archival to match the required recovery window. This maintenance
does not create a backup schedule or prove 48-72 hour stability.

## Rollback

Record pre-change effective and persisted settings. To restore the audited old
policy after a fresh safety check, run apply with retention 2592000, size
1073741824 and compression OFF, then synchronize source/environment values.
If settings were not previously persisted, a DBA can use `RESET PERSIST IF EXISTS`
for these four variables after reviewing startup precedence; it does not undo
their current runtime values. Never restore an old whole `mysqld-auto.cnf` blindly.

Purging is not undone by lengthening retention. Historical recovery must use
the verified off-server dump and archived binlogs in an isolated environment,
with MySQL 8.0.46-compatible tools. Restoring production itself requires a write
freeze, reconciliation of subsequent writes, a fresh backup, and separate review.

## Primary References

- [MySQL purge semantics](https://dev.mysql.com/doc/refman/8.0/en/purge-binary-logs.html)
- [Retention and rotation](https://dev.mysql.com/doc/refman/8.0/en/replication-options-binary-log.html)
- [Transaction compression](https://dev.mysql.com/doc/refman/8.0/en/binary-log-transaction-compression.html)
- [Persisted settings](https://dev.mysql.com/doc/refman/8.0/en/persisted-system-variables.html)
