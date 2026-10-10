# Rebuild and Container Recreation Guidance

## Request

The user asked for the missing rebuild/restart step after SFTP uploads. This is
deployment guidance, not authorization to execute a production publication.

## Verified Context

- Local `docker/compose.sh prod` loads `.env.production`, selects the
  `partsmall-prod` Compose project, and merges base and production Compose.
- App and web have build targets; queue uses the same app image and the
  production `workers` profile.
- Entrypoint rebuilds the Laravel configuration cache for production processes.
- Read-only container inspection confirms both live app and queue still use
  `CACHE_STORE=database` and `QUEUE_CONNECTION=database`.
- Root disk has reached 85% used with approximately 5.5 GB free. Current disk
  headroom and build cost must be reviewed before this first publication.

## Routine Code-Only Publication

These commands are a routine workflow ONLY after fresh backups/rollback material,
adequate build space, uploaded sources/helper scripts, existing dependency
services/volumes, and the queue transition have been verified. Run each command
individually and stop on failure.

```sh
ssh partsmall-server
cd /var/www/partsmall-releases/production-20261008
sudo sh docker/compose.sh prod build app web
sudo sh docker/compose.sh prod --profile workers up -d --no-deps --force-recreate --wait --wait-timeout 180 app web queue
sudo sh docker/compose.sh prod --profile workers ps
curl -I https://partsmall.ir/up
```

The recreation command is intentionally scoped to app/web/worker. `--no-deps`
does not recreate MySQL or provision Redis; required dependencies must already
be available. Existing volumes are retained. Do not run `down -v` or pruning.
A plain container restart neither builds uploaded sources into an image nor
applies changed Compose environment/configuration.

## Current Release Exception

This upload contains first-time dedicated Redis services, a new external queue
volume, environment changes, and worker settings. It must not be treated as a
routine code-only recreation. Review disk headroom, take a fresh pre-deployment
backup, provision the Redis services/volume, retain old database workers through
the producer transition, and verify drain/retries before retiring old workers.
Do not shorten binlog retention or purge logs without the pending recovery review.
The controlled transition is documented in `docker/ops/REMEDIATION.md`.

## Outcome

Only read-only server checks and this report were performed. No build, upload,
service recreation, cache flush, job consumption, database mutation, or deletion
was executed. Completion of the user's SFTP upload remains unconfirmed.

References:
[Compose up](https://docs.docker.com/reference/cli/docker/compose/up/) and
[Compose restart](https://docs.docker.com/reference/cli/docker/compose/restart/).
