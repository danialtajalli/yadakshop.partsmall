# Partsmall Production Binlog Policy

Scope: `partsmall-prod-mysql-1`, volume `partsmall-prod-mysql`, MySQL 8.0.46.
Host MySQL, rehearsal, other websites, cache drivers, and queues are not changed.

## Safety Gate

Before every mutation, take a fresh off-server SQL backup with source coordinates,
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
