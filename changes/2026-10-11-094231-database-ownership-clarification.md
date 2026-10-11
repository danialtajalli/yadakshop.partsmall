# Database, cache and queue ownership clarification

## Request

Clarify whether the previously reported large binlogs belong to Docker MySQL or
host MySQL, and identify the website/application whose cache and queue settings
were inspected.

## Clarification From the Previous Verified Audit

- Website: Partsmall, partsmall.ir, production Compose project partsmall-prod.
- Laravel application inspected: partsmall-prod-app-1.
- Production database: partsmall, on Docker service mysql, container
  partsmall-prod-mysql-1.
- The approximately 16.1 GiB of binlogs were in this Docker database's volume:
  /var/lib/docker/volumes/partsmall-prod-mysql/_data/binlog.*.
- Partsmall's effective Laravel application cache.default and queue.default
  were both database. Cache writes were attributed to partsmall.cache in that
  Docker MySQL binlog, not to another website or the host database.
- Partsmall's dedicated queue worker is named partsmall-prod-queue-1. The
  previous effective-setting probe ran in the app container, not separately in
  the worker; this clarification does not claim a new worker-runtime check.
- The separate host MySQL service stores its data in /var/lib/mysql and serves
  other application databases. Its measured binlogs were about 0.4 GB, not the
  16.1 GiB identified as the main consumer.
- Partsmall rehearsal has another separate Docker MySQL container and volume;
  it was not the source of the production growth finding.

Known production release path:
/var/www/partsmall-releases/production-20261008.

## Actions, Backups and Outcome

No fresh SSH inspection was necessary for these ownership questions; all facts
above are from the previous audit and not asserted as a new live-state sample.
No server or database changes, deletion, restart or deployment occurred. No new
backup was needed, and existing backups remain untouched. Only this local report
was created. No credentials or sensitive row contents are included.
