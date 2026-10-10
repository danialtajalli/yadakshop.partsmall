# Partsmall Docker

The stack runs Nginx, PHP 8.4-FPM, a Redis queue worker, MySQL, and separate Redis
services for cache and durable queues. Development also runs Vite. The existing
host `.env`, MySQL database, and uploads are not modified by setup. Horizon and
Meilisearch are not required.

## Development on Windows

Run from the repository root with Docker Desktop using Linux containers:

The helpers use project name `partsmall-dev`. If you already have data from
the old helper (project `partsmall`), set `$env:COMPOSE_PROJECT_NAME='partsmall'`
before these commands to reuse those development volumes. A different project
name creates separate volumes; it does not move or delete existing data.

```powershell
php docker/init-env.php
./docker/compose.ps1 build
./docker/compose.ps1 run --rm setup
./docker/compose.ps1 run --rm import-uploads
./docker/compose.ps1 up -d --wait
```

If Windows blocks PowerShell scripts, invoke the helper using
`powershell -NoProfile -ExecutionPolicy Bypass -File docker/compose.ps1` followed
by the same arguments. This applies only to that invocation and does not change
the machine's execution policy.

Open http://localhost:8000 and http://localhost:8000/admin. The database starts
empty; migrations create the schema. Create an administrator interactively:

```powershell
./docker/compose.ps1 exec app php artisan make:filament-user
```

If PHP is not installed on the host, initialize the environment with Docker:

```powershell
docker run --rm --mount "type=bind,source=$($PWD.Path),target=/work" -w /work php:8.4-cli php docker/init-env.php
```

Source changes are mounted into the development containers. The queue listener
reloads PHP for each job. Vendor packages, node modules, compiled views, database
files, and uploads use Docker volumes, avoiding host Windows dependencies.
Vite uses polling for Docker Desktop file changes and removes `public/hot` when
stopped. After changing Composer dependencies, rebuild and run `setup` again;
after changing frontend dependencies, restart `vite`.

Useful commands:

```powershell
./docker/compose.ps1 logs -f app queue vite
./docker/compose.ps1 exec app php artisan test
./docker/compose.ps1 down
```

`down` preserves data. `down --volumes` deletes the Docker database and uploads.
Change `WEB_PORT` and `APP_URL` together if port 8000 is occupied. For LAN access,
set `WEB_BIND_ADDRESS=0.0.0.0`, `APP_URL` to the computer's LAN URL, and
`VITE_HMR_HOST` to its LAN hostname or IP, then recreate the containers.

## Ports

| Container | Internal port | Published host port |
| --- | --- | --- |
| `web` (Nginx) | `80` | `127.0.0.1:8000` |
| `vite` (development only) | `5173` | `127.0.0.1:5173` |
| `app` (PHP-FPM) | `9000` | Not published |
| `mysql` | `3306` (SQL), `33060` (X Protocol) | Dev: not published; prod SQL: `127.0.0.1:3307` |
| `queue`, `setup`, `import-uploads` | None | Not published |

Nginx connects to `app:9000`; Laravel connects to `mysql:3306`, not the host
database. Health checks use each service's internal port. Queue and setup
inherit the PHP image's `9000/tcp` metadata but do not start PHP-FPM or listen
on that port. Production does not run Vite.

`VITE_PORT` sets the Vite listener, published port, health check, and browser
HMR port together. `APP_URL` must match the browser-facing web URL for Laravel
URLs and Vite's CORS configuration. The defaults match native Laravel
development (`8000` and `5173`); stop Madeline before starting Partsmall.
When `APP_URL` uses a loopback address, Vite also permits `localhost`,
`127.0.0.1`, and `[::1]` at that same web port. Other origins remain restricted.

## Linux or macOS

Use the shell helper, which selects the environment, project, and overlay:

```sh
php docker/init-env.php
sh docker/compose.sh dev build
sh docker/compose.sh dev run --rm setup
sh docker/compose.sh dev run --rm import-uploads
sh docker/compose.sh dev up -d --wait
```

For existing development volumes named `partsmall_*`, export
`COMPOSE_PROJECT_NAME=partsmall` before using the helper. When invoking Compose
directly, always specify `--env-file` and `DOCKER_ENV_FILE` so interpolation and
container environments use the same file instead of the live host `.env`.

## Production

Read [the existing-server cutover runbook](DEPLOYMENT.md) before migrating a live
installation. The production helper combines `compose.yaml` and
`compose.prod.yaml`, selects `.env.production`, and uses project `partsmall-prod`.
Create `.env.production` from `.env.production.example`, preserve the live
`APP_KEY`, and fill in credentials and live integration settings. Do not use
`init-env.php` to generate a replacement key for an existing installation.

Production builds frontend assets into images and caches Laravel configuration.
Both web and SQL ports are pinned to localhost; the host NGINX terminates HTTPS.
Keep `APP_URL` equal to the public HTTPS URL. Review `DOCKER_SUBNET` for conflicts;
Laravel trusts forwarded headers only from that network. Compose requires 2.24.4
or newer, and Docker Engine should be 28 or newer.

For a NEW, empty installation only:

```sh
docker volume create partsmall-prod-mysql
docker volume create partsmall-prod-storage
docker volume create partsmall-prod-uploads
docker volume create partsmall-prod-redis-queue
sh docker/compose.sh prod build
sh docker/compose.sh prod run --rm setup
sh docker/compose.sh prod --profile workers up -d --wait
```

For an EXISTING installation, restore the database before `setup`, import uploads,
validate the application, then enable workers, as described in the runbook.
On Windows, use `./docker/compose.ps1 -Production` with the same subcommands.
The helpers separate development and production volumes. Production uses external
volumes which survive `down --volumes`; they are not backups. Adjust volume names
if changing `PRODUCTION_VOLUME_PREFIX`. Workers require `--profile workers` in
production, so restored jobs do not execute during the cutover checks.
Migrations run only through `setup`, not in every web or worker container.
Run `setup` once per deployment, before restarting workers.

Redis uses internal service names `redis-cache` and `redis-queue`, with no
published ports. Cache has an eviction policy and memory ceiling. Queue Redis
uses `noeviction`, AOF persistence, and an external volume. Container logs are
bounded to 20 MB times 5 files by default; existing containers require recreation.

For an existing database-queue deployment, follow
[the remediation and queue-transition runbook](ops/REMEDIATION.md). Keep an old
database worker until all ready, reserved, and delayed jobs have drained. Set
`CACHE_STORE=redis` and `QUEUE_CONNECTION=redis` in the existing selected
environment file; do not regenerate its credentials or application key. Existing
`database` values are respected to permit a controlled transition.

The production worker has a 120-second job timeout, a 180-second retry interval,
and a 150-second shutdown grace period. MySQL listens internally on `3306`, and
the host-only SQL port `3307` lets existing phpMyAdmin add a second server without
changing the host MySQL. See `phpmyadmin-server.php.example` and the runbook.
Application logs go to container stderr.

The PHP-FPM master uses the official image's default root account to open its
log stream, while its request workers run as `www-data`. Artisan commands and
the queue worker also run as `www-data`. Use `exec --user www-data app ...`
when running commands that write files in production.

The Docker example uses `CONTACT_PIPELINE=database_only` so local forms work
without external credentials. For Didar, set `CONTACT_PIPELINE=didar_with_database`,
`DIDAR_API_KEY`, and `DIDAR_DEAL_OWNER_USERNAME` in the selected environment file.
Supply your
ARCaptcha keys there as well. Other Laravel environment variables can be added
to the same file. Recreate app and queue containers after configuration changes.

Uploads are mounted at both `storage/app/public` and `public/panel/assets/uploads`.
This preserves existing image URLs and QR code writes without a Windows junction.
Nginx serves uploads directly and does not execute uploaded PHP files.
Both `/panel/assets/uploads/` and the legacy `/storage/` URLs use the same volume.

## Import Existing Uploads

The upload volume is separate from the host's `storage/app/public`. Images are
not baked into Docker images or copied automatically when containers start.
Import them after building the images, even when using a fresh Docker database:

```powershell
./docker/compose.ps1 run --rm import-uploads
```

This copies missing files and folders, preserves existing Docker files, and
sets ownership for PHP to write uploads. It does not change the host files or
import database records. It is safe to rerun when additional host images arrive.
Set `UPLOAD_SOURCE` to another existing upload directory to import a backup.
The import runs without starting the application or database containers.

## Import Existing Data

Use a current Laravel database dump, not the legacy `partsmall_db.sql` export.
Back up the source database and uploads first. Import before running `setup`
on an existing installation; migrations then bring the imported schema up to date.
Retain the source installation's `APP_KEY` if migrating its encrypted data.

Stop app, queue, and Vite before importing, then start MySQL:

```powershell
./docker/compose.ps1 stop app queue vite
./docker/compose.ps1 up -d mysql
$dbContainer = ./docker/compose.ps1 ps -q mysql
docker cp 'C:\backups\partsmall.sql' "${dbContainer}:/tmp/partsmall.sql"
./docker/compose.ps1 exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" mysql -u "$MYSQL_USER" "$MYSQL_DATABASE" < /tmp/partsmall.sql'
./docker/compose.ps1 run --rm setup
./docker/compose.ps1 run --rm import-uploads
./docker/compose.ps1 up -d
```

Avoid importing active production queue jobs into development; review the
dump's `jobs` table first. The upload importer preserves files already in Docker;
restore into an empty upload volume when replacing an installation completely.

## Backup and Restore

These commands use files inside the container before copying to the host, so
PowerShell does not change SQL encoding or binary archive contents:

```powershell
New-Item -ItemType Directory -Force docker/backups
./docker/compose.ps1 exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" mysqldump --single-transaction --no-tablespaces -u "$MYSQL_USER" "$MYSQL_DATABASE" > /tmp/partsmall.sql'
$dbContainer = ./docker/compose.ps1 ps -q mysql
docker cp "${dbContainer}:/tmp/partsmall.sql" docker/backups/partsmall.sql
./docker/compose.ps1 exec -T app tar -czf /tmp/uploads.tar.gz -C storage/app/public .
$appContainer = ./docker/compose.ps1 ps -q app
docker cp "${appContainer}:/tmp/uploads.tar.gz" docker/backups/uploads.tar.gz
```

Store a protected copy of `.env.docker` with the backups. Restore SQL using the
import procedure above. To restore uploads, stop app and queue, copy the archive
to a created app container, then extract and repair ownership:

```powershell
docker cp docker/backups/uploads.tar.gz "${appContainer}:/tmp/uploads.tar.gz"
./docker/compose.ps1 start app
./docker/compose.ps1 exec -T --user root app sh -c 'tar -xzf /tmp/uploads.tar.gz -C storage/app/public && chown -R www-data:www-data storage/app/public'
./docker/compose.ps1 up -d
```

For a consistent backup, pause writes while backing up the database and uploads.
Restore into empty volumes when replacing an installation completely.
