# Server Remediation and Queue Transition

Follow root `AGENTS.md`: verified backups before vital database changes, protect
other websites, delete only confirmed redundant Partsmall files, and report each
server-related prompt in `changes/`.

## Backups and Rollback

Capture the production database, host application databases, uploads/private
storage, release, protected environment files, effective Compose configuration,
and exact app/web/MySQL images. `backup-*.sh` emits data to stdout for a protected
off-server destination. The SQL scripts assume audited InnoDB application tables;
use Bash where specified so gzip cannot hide a failed dump. Never print or commit
backup contents. Preserve earlier backups.

Check gzip integrity, then restore SQL in isolated MySQL with no production
volume, no published port, binary logging disabled, and event scheduling disabled.
Compare important table counts. `restore-test-start.sh` refuses to overwrite an
existing validation target. Do not run integration workers on restored databases.

For changes requiring a write freeze, pause Partsmall producers, let in-flight
jobs finish, then take a final consistent snapshot. Maintenance responses alone
do not stop workers, cron, or external writers. Preserve prior images/settings.
Rollback after new writes requires reconciling those writes and both queue stores.

## Cache and Redis

Affected content caches use version-two plain-row keys and hydrate models after
reading arrays. Keep class unserialization disabled. TTL is 24 hours; hits do not
extend expiry. Content changes invalidate tags; unchanged saves do not. Repeat
the same request to verify one build followed by hits and a rebuild after an
actual content change or expiry. Use `runtime-audit.php` in both app and worker
to inspect effective drivers/hosts and cache types without printing payloads.

Create `${PRODUCTION_VOLUME_PREFIX}-redis-queue` before starting the new stack.
Cache Redis uses `allkeys-lru` and a memory cap. Queue Redis is a separate process
using `noeviction`, AOF every-second syncing, and an external persistent volume.
Monitor rejected writes and memory: a full noeviction server can reject new jobs.
Every-second AOF can lose approximately one second of writes after a crash.

## Queue Transition

1. Inventory every database queue and its ready/reserved/delayed counts. Inventory
   the old host Redis namespace too. `legacy-queues.sh` reports metadata without
   consuming jobs. Preserve host Redis used by other sites such as `cheragh-bot`.
2. Keep current database workers draining while new Redis services start. Start a
   separately named temporary worker explicitly consuming the old `database`
   connection and every observed queue name through the producer transition.
   Preserve legacy Redis workers if pending Partsmall jobs exist there.
3. Update the existing environment to `CACHE_STORE=redis` and
   `QUEUE_CONNECTION=redis`, retaining credentials, key, and live integrations.
   Build and recreate app/web/worker using reviewed settings. Setup/migrations
   require the verified database backup.
4. Verify both processes use Redis, ping both connections, and test a controlled
   job with failure/retry. Recount old queues until all ready, reserved, and
   delayed work drains. `--stop-when-empty` alone cannot settle future delayed jobs.
5. Retire only the temporary old workers after their queues are empty. Preserve
   old stores and backups for rollback. No queue-clearing command is required.

After effective Redis settings are verified, run `php artisan partsmall:queue-probe
--fail-first`, then read its returned UUID with `php artisan partsmall:queue-probe
--status --id=<UUID>`. Expect two attempts and completed=1. It uses expiring Redis
markers, not application data; verify the deliberate first failure in worker logs.
This probes worker retries, not idempotency of real business operations.

Worker timeout is 120 seconds, retry_after is 180 seconds, and shutdown grace is
150 seconds. Retried external operations require effect-boundary deduplication.
Didar deal creation still needs a reviewed API idempotency/reconciliation strategy
for a crash between remote success and saving its returned ID. Redis cannot
guarantee exactly-once external effects.

## Retention, Monitoring, and Acceptance

Production Compose provides `MYSQL_BINLOG_EXPIRE_LOGS_SECONDS`, preserving the
audited production value 2592000 (30 days) until the recovery window is approved.
The proposed three-day value is 259200; confirm backup/PITR and replication needs
before shortening it. Database dumps alone do not provide a complete PITR chain.
Apply by controlled recreation and verify the effective variable; check whether
persisted MySQL settings override it. Review any `PURGE BINARY LOGS` operation
separately after backup validation. Never delete binlog files from disk.

Container logs are limited to 20 MB times 5 files. App, Nginx, and PHP container
logs use stdout/stderr. Inspect legacy file-log rotation and shared journal limits
before changing them. Define business retention and archives for `request_logs`
before deleting records; no automatic deletion is configured without that decision.

Record disk bytes/inodes, binlog growth per hour/day, queue age/backlog, Redis
memory/rejected writes, backup age, database locks, and each site's actual HTTP
status/latency. `server-baseline.sh` and `http-baseline.cjs` provide initial probes.
Responses may legitimately be redirects or method/authentication responses.
Set disk alerts at 80%/90% plus estimated time to exhaustion. A durable alert
destination and scheduled daily off-server backups are required; a laptop download
alone is not a scheduled backup. Inventory cron and use one scheduler owner.

After deployment, verify login, products, uploads, queues, and important operations.
Observe 48-72 hours including peak traffic and a complete retention cycle.
Acceptance requires a tested restore, cache reuse, no growing queue backlog,
expected site responses, bounded logs, and at least 20% free disk throughout
the retention cycle. Source changes and one health check do not satisfy this.

## Primary References

- [Laravel Redis](https://laravel.com/docs/13.x/redis)
- [Laravel queue timeouts](https://laravel.com/docs/13.x/queues)
- [Redis eviction](https://redis.io/docs/latest/develop/reference/eviction/)
- [Redis persistence](https://redis.io/docs/latest/operate/oss_and_stack/management/persistence/)
- [Docker log rotation](https://docs.docker.com/engine/logging/drivers/json-file/)
- [MySQL binlog retention](https://dev.mysql.com/doc/refman/8.0/en/replication-options-binary-log.html)
