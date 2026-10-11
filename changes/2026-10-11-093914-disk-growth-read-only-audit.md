# Disk usage and recurring growth: read-only SSH audit

## Request and Scope

The user reported that the server had recently filled, storage was expanded,
and usage was now near 50%. Identify current storage consumers and ongoing
growth. All checks were read-only on partsmall-server, 130.185.122.197.
No cleanup, service restart, deployment or configuration change was requested
or performed. Checks took place approximately 06:01-06:09 UTC on 2026-10-11
(09:31-09:39 Asia/Tehran).

## Verified Filesystem State

- Root /dev/vda3, ext4: 80,139,071,488 bytes total, 37,363,744,768 used,
  39,333,560,320 available, reported 49% used at the final check.
- Rounded display: 75 GiB total, 35 GiB used, 37 GiB available. The prior
  inspection had approximately 38 GiB total; capacity has approximately doubled.
  The user's description of adding 50% storage is not used as a measured size.
- Inode usage was only 2%; this is not an inode-exhaustion problem.
- All eight existing containers were running after a recent restart; production
  app, web and MySQL healthchecks were healthy. No Redis Docker container appeared
  in the inventory.

## Main Consumers

Allocated sizes below are approximate snapshots from du, not additive Docker
logical image estimates:

| Path / Resource | Bytes | Approx. GiB | Notes |
| --- | ---: | ---: | --- |
| Production MySQL volume | 17,950,244,864 | 16.72 | Main consumer |
| Production binlogs | 17,237,269,813 | 16.05 | Subset of the MySQL volume |
| Production table directory | 434,491,392 | 0.40 | Not the main growth source |
| /var/lib/containerd | 5,148,053,504 | 4.79 | Image/snapshot/build storage |
| /var/www | 4,225,339,392 | 3.94 | All websites, including Partsmall |
| /var/lib/mysql | 2,042,052,608 | 1.90 | Shared host MySQL; unrelated sites depend on it |
| Rehearsal MySQL volume | 967,041,024 | 0.90 | Its binlogs were only about 4 MB |
| /var/log | 755,761,152 | 0.70 | Journal about 416 MiB; Nginx about 99 MiB |
| Docker container directories | 45,559,808 | 0.04 | Logs/metadata not the principal problem |

Within /var/www, agent-yadakgate/storage/app/public/uploads occupied approximately
2.20 GiB. These are another website's uploads, not confirmed redundant files.
Partsmall's old checkout occupied about 555 MiB and its release directories
together about 53 MiB. Recovery backups under /home/ubuntu remain preserved.

Docker reported 3.542 GB of logical images and 1.576 GB build cache, with
approximately 1.011 GB of build cache marked reclaimable. These numbers overlap
physical containerd storage and must not be added as separate disk consumers.
Nothing was pruned; reclaimable does not establish rollback safety or redundancy.

The /srv/sftp folders are bind mounts of existing /var/www paths. Their roughly
5.15 GB apparent total is duplicate accounting, not additional stored copies.
findmnt verified the source paths. Whole-root du therefore overcounts these
mounts relative to df; df is authoritative for filesystem occupancy.

lsof found no significant deleted-but-open regular files; reported deleted
temporary files had zero size. No hidden multi-GB deleted log was found.

## Binlog Growth and Retention

Production MySQL 8.0.46 had 17 binary logs, binlog.000010 through binlog.000026.
Automatic purge is enabled, but retention is still 2,592,000 seconds (30 days).
Maximum individual binlog size is 1 GiB. No active Binlog Dump sender was found;
this does not by itself authorize purging or exclude backup/PITR requirements.

Two SHOW BINARY LOGS snapshots:

| UTC time | Total binlog bytes |
| --- | ---: |
| 06:04:50 | 17,212,253,882 |
| 06:06:18 | 17,237,269,813 |

Growth was 25,015,931 bytes in 88 seconds: approximately 1.02 GB/hour or
0.953 GiB/hour. If sustained, that is about 22.9 GiB/day and roughly 38 hours to
consume the final snapshot's currently available space. This is a short-sample
projection, not a measured daily rate or a guaranteed exhaustion time. Loads,
retention, cleanup and other disk consumers can change it.

## Write Attribution and Running Cache Defect

The existing host mysqlbinlog utility (8.0.46) decoded the current active binlog
through an aggregation filter. Only table names, operation counts and aggregate
decoded row-text lengths were emitted; row values and credentials were not
printed or saved. The MySQL container itself lacks mysqlbinlog; no package was
installed and no new container was created.

| Table | Aggregate decoded row-text bytes |
| --- | ---: |
| partsmall.cache | 126,068,953 |
| partsmall.sessions | 7,135,743 |
| partsmall.jobs | 753,812 |
| partsmall.request_logs | 62,657 |
| partsmall.shops | 11,196 |

Cache accounted for approximately 94.06% of the sampled decoded row payload.
This is not an exact percentage of binary file bytes. There were 2,702 cache
DELETE, 2,808 INSERT and 1,374 UPDATE row events in that active-log sample.
The file was active during inspection, so counts are a point-in-time sample.

Read-only application queries verified the effective connection is the Docker
production MySQL service and database partsmall. The server UUID matched the
root MySQL check, preventing confusion with the old host database.

Effective running application settings still show:

- cache.default = database
- cache.serializable_classes = false
- queue.default = database
- database.redis.cache.host = 127.0.0.1
- database.redis.queue.host is unset

Of 30 content-cache rows sampled without printing their keys or values, nine
decoded as __PHP_Incomplete_Class and 21 as arrays. The running SafeCache lacks
the plain-value and row-list validation methods present in the local fix.
Its normalized source SHA-256 differs from the checkout, proving the difference
is not merely Windows line endings. The prepared cache remediation is not
present in the currently running SafeCache implementation.

Exact current counts were approximately 6,322 cache rows, 3,061 request-log rows,
zero pending database jobs and zero failed jobs. information_schema table_rows
estimates were much larger and stale; they were not treated as actual counts.
Allocated table storage can remain large even when current row counts are lower.

Conclusion: retained production binlogs are the main stored data, and frequent
database-cache writes dominate the sampled ongoing growth. The cache defect is
still observable in production. The current evidence explains a continuing
capacity risk; it does not reconstruct the precise historical peak immediately
before the user's storage expansion.

## Outcome, Backups and Remaining Work

No server files, databases, persistent volumes, Docker images, backups, shared
service settings or unrelated websites were changed or deleted. No seeder,
migration, binlog purge, OPTIMIZE TABLE or Docker prune was executed. Temporary
audit helpers were created only outside the local project and streamed through
SSH; no audit helper was saved on the server. This report is the only repository
file created for the request. No fresh backup was needed for these read-only
checks; existing backups were untouched.

Next steps, not performed: deploy and verify cache reuse remediation; prepare
the separate Redis cache/queue rollout with its safe queue handoff; agree on
binlog retention consistent with verified off-server backups and recovery needs
before any MySQL-managed purge. Current production container log options are
also uncapped, although their present size is small. Do not delete binlog files
manually or remove other websites' uploads to compensate for ongoing cache writes.

Some individual SSH connections timed out and were retried. All needed audit
commands completed or returned their failures; no audit process is left running.
