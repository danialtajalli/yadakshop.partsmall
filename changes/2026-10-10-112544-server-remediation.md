# Team Lead Server Checklist: Remediation Record

## Request and Scope

Audit and address the eight-part checklist: backups, cache, Redis, queues, binlogs,
bounded logs, service isolation, and monitoring. Verified production directory:
`/var/www/partsmall-releases/production-20261008` on `ubuntu@130.185.122.197`.

## Verified Baseline

- Root disk: 79% used; initially 8,248,770,560 bytes available; inode use 4%.
- RAM: approximately 11.7 GiB total, 7.0 GiB available; no swap.
- Production MySQL: 8.0.46, 30-day binlog retention, about 10.55 GB of binlogs
  at 07:58 UTC. Host MySQL already retains binlogs for three days.
- No production InnoDB transactions were pending at initial inspection.
- App/worker: database cache and queues; Redis hosts currently container-local
  `127.0.0.1`. Cache class unserialization is disabled.
- Cache table: 48,437 rows. Sampled catalog/directory model lists decode as
  `__PHP_Incomplete_Class`, confirming the cache defect.
- Request logs: 375,182 rows; five database jobs, none reserved; no failed jobs.
  Live traffic changes these counts.
- Partsmall Docker logs have no size/file limits. Host Redis is shared with
  `cheragh-bot`; it must not be flushed or replaced for Partsmall.
- Read-only Redis scans found no matching queue list/zset keys in DB 0-15 at
  inspection time; this does not settle historical jobs.
- Production/rehearsal use separate directories/volumes but shared image tags.
  Rehearsal remains running. Host Docker/Nginx/MySQL/Redis are enabled and active.
- Nginx syntax validation passed.

## Other Websites and Dependencies

- `bot.cheraghbargh.ir`: `/var/www/cheragh-bot`, host `cheragh_bot` database,
  host Redis DB 0/1, prefix `telegram_bot`, host PHP-FPM.
- `ruby100.ltd`: `/var/www/ruby100`, host `ruby100` database, host PHP-FPM.
- `agent.yadakgate.ir`: `/var/www/agent-yadakgate`, host `yadakgate_agent` database.
- `price.yadakgate.ir`: `/var/www/price-yadakgate`, host `yadakgate_price` database.
- Other roots include `yadakgate`, `partner-yadakgate`, `robot--yadakgate`.
  `files.partsmall.ir` proxies to Filestash on 8334.
- External HEAD baseline: Partsmall root/up 200, admin 302; bot/ruby100/yadakgate/
  agent/robot 200; price 302; partner 400; Filestash 405. These observations do
  not establish the intended business response for every route.

## Source Changes Prepared

- Version-two plain-row caches; hydrate models after reading, recursively reject
  objects, and avoid invalidation on unchanged saves.
- Removed testing bypasses so feature tests exercise actual reuse.
- Separate internal Redis cache/durable queue, memory policies, PhpRedis image
  extension, connection settings, 180-second retry versus 120-second timeout,
  graceful shutdown, and worker/dependency health checks.
- Container log limits and configurable binlog retention in source Compose.
- Consistent Docker environment templates and production initializer.
- Audits, streaming backup scripts, and `docker/ops/REMEDIATION.md` runbook.

## Backups and Decisions

The backup-destination preference was requested. For the initial off-server copy,
the protected local folder is
`C:/Users/BABAK/Documents/partsmall-backups/2026-10-10-1145`.
Inherited directory permissions were removed and access granted to its owner.
Sensitive backups remain outside the repository/deployment.

Completed transfers and gzip checks at this stage:

- Production SQL: 23,533,966 bytes; SHA-256
  `5c506871c7335a6f47ccbf24f7fe3e92b271878df05abbd71caf19f8025fce58`.
- Host application SQL: 58,796,330 bytes; SHA-256
  `7e2c7dfd2c5acf318acfff04d638ab3e8b1e4ee0c116e8a41c5b83470341838b`.
- Uploads/private storage: 34,971,463 bytes; SHA-256
  `ee4d4988add15294a5cb519a2b76b3d8d43ff3759472c51048ab6b99e95b034d`.
- Effective current Compose JSON: privately captured off-server.

Additional completed transfers, all gzip archives integrity-checked:

- Release/service configuration: 295,522,494 bytes; SHA-256
  `6507ba1504552781f378110e1b92b5485e8ae83ff4860222040d36d6aa223ee4`.
- Production images: 509,717,713 bytes; SHA-256
  `b388ab86833f330f59949c8cf77e69d5ea500b62c96c7a6bbc0b1fcd7e380af3`.
- Earlier recovery backups: 210,122,965 bytes; SHA-256
  `d2372d1e5091fabf80de9e15f89ae05aeca16e165f125d8a3c3db6d59387b6e2`.
- Effective Compose JSON: 12,710 bytes; SHA-256
  `65b278cd9a3f758c2e1c1e0e55a51da146fd771c83ae7e16a4de941817bc4490`.

Both SQL dumps passed footer and gzip checks. Earlier server backups were
preserved in place and copied off-server. Current configuration and image
archives were transferred and integrity-checked, not redeployed.

Production SQL restored into an isolated MySQL container with network disabled,
no published port, binary logging disabled, and event scheduling disabled.
Its 37 tables and important counts matched production: companies 84, cars 509,
models 358, parts 303, shops 358, users 19. Host application SQL then restored
into the same disposable container, replacing only its test `partsmall` schema.
Schema/table counts matched the host: cheragh_bot 39, partsmall 37, ruby100 17,
yadakgate_agent 11, yadakgate_price 12. `CHECK TABLE ... QUICK` reported OK for
all 116 restored application tables. This is a restore/integrity check, not a
full end-user workflow test or PITR validation.

Only the newly created `partsmall-restore-validation-20261010` container and
same-named volume were removed after validation. Cleanup verified purpose labels,
network isolation, exact mount, and sole volume consumer before deletion. No
production/rehearsal volume, existing image, release, or backup was removed.

Rollback material is the protected release/config archive, effective Compose,
SQL/uploads, and saved images. Captured image IDs:

- App/worker: `sha256:0de8570765004413e4c1d3b051169b350b2e3bd1f447a8059ed9738613f2c324`.
- Web: `sha256:0ddc4cb90828c8d16ac00094d95d808fc6386b83e1f9d92f896e2e96c3c2ff7c`.
- MySQL: `sha256:7dcddc01f13bab2f15cde676d44d01f61fc9f99fe7785e86196dfc07d358ae2b`.

Before a production write-changing release, take a fresh final snapshot and define
producer/worker pause and write reconciliation. Initial backups do not cover
writes arriving after their capture.

Business request-log retention, binlog/PITR needs, durable daily backup storage,
and alert delivery remain unresolved. No records/binlogs have been purged and no
shared Redis, Nginx, or host MySQL configuration has been changed.

## Verification and Remaining Work

Focused cache/Docker tests: 20 passed, 152 assertions. Two existing product-listing
tests also fail with unchanged `HEAD` ProductService, independently of this change.
The third previously observed ProductShowTest contact-text assertion also fails
when unchanged `HEAD` ProductService is loaded. These three failures were not
silently fixed or counted as passing.

Broader cache/content/contact/request-log/Docker suite: 99 tests, 98 passed,
one skipped, 424 assertions. Queue probe job/command suite: six passed,
26 assertions. The controlled probe supports first-attempt failure, retry, and
an expiring completion marker; it has not run through a live production worker.
Compose parsing tests passed, but Docker Desktop is not running locally, so a
new image build/live Redis integration test remains required. `git diff --check`
passed. Pint found preexisting style issues in five service files; formatting
was confined to new audit/probe code rather than unrelated service rewrites.
Final focused rerun after preserving the binlog default: 26 tests passed,
178 assertions (cache reuse, cache validation, Docker settings, and queue probe).

At 08:40:52 UTC, after disposable restore cleanup, root disk was 80% used with
7,776,972,800 bytes available. Binlogs grew approximately 0.44 GB over about
43 minutes (roughly 0.6 GB/hour); this is a short sample, not a daily baseline.
Effective retention remained 2,592,000 seconds (30 days). Prepared Compose and
initializer preserve that value until the proposed three-day recovery window
is approved. No binlogs were purged. At 08:41 UTC, external HEAD responses
matched the initial baseline for all eleven tested URLs.

## Checklist Status

1. Initial off-server backups and isolated SQL restore verification completed;
   final deployment-time snapshot and full rollback rehearsal remain.
2. Cache defect confirmed in production and fixed/tested in local source;
   live cache reuse/write reduction cannot be accepted before deployment.
3. Dedicated Redis services, durable queue volume, environment settings, and
   health checks prepared; Redis is not yet running for production Partsmall.
4. Legacy queue baseline captured and transition/probe documented. Live drain,
   retries, business-operation idempotency, and scheduler ownership remain.
5. Binlog growth/retention audited. Retention approval, persistent deployment,
   safe MySQL purge, and hourly/daily measurements remain.
6. Docker log limits prepared in source. Existing container settings are unchanged;
   host/file log rotation, journal cap, and request-log retention remain.
7. Shared dependencies inventoried. Existing rehearsal/releases/images retained;
   cleanup requires dependency/rollback review, and FPM capacity needs load data.
8. External HTTP and disk baselines recorded. Durable daily backups, alerts,
   authenticated/upload/business smoke tests, and 48-72 hours remain.

Deployment is not yet complete. Remaining acceptance work includes a controlled
queue transition and retry probe, external-operation idempotency review,
scheduled backups/alerts, live login/upload/business checks, and 48-72 hours of
observation with a complete retention cycle. These are not marked complete.

No production application release, queue switch, shared service configuration,
or vital production database change was performed during this prompt. Server
writes were limited to the isolated restore-test resources, now removed.
The root-cause fix still needs a controlled production release; the server's
disk growth remains urgent and the 20% free-space acceptance margin is not met.
