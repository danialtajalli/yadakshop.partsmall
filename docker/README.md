# Partsmall Docker

The stack runs Nginx, PHP-FPM, a database queue worker, and MySQL. Development
also runs Vite. The existing host `.env`, MySQL database, and uploads are not
modified by setup. Redis, Horizon, and Meilisearch are not required by the
current application configuration.

## Development on Windows

Run from the repository root with Docker Desktop using Linux containers:

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
docker run --rm --mount "type=bind,source=$($PWD.Path),target=/work" -w /work php:8.3-cli php docker/init-env.php
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
| `mysql` | `3306` (SQL), `33060` (X Protocol) | Not published |
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

The same setup works with native Compose commands:

```sh
php docker/init-env.php
docker compose --env-file .env.docker -f compose.yaml -f compose.dev.yaml build
docker compose --env-file .env.docker -f compose.yaml -f compose.dev.yaml run --rm setup
docker compose --env-file .env.docker -f compose.yaml -f compose.dev.yaml run --rm import-uploads
docker compose --env-file .env.docker -f compose.yaml -f compose.dev.yaml up -d --wait
```

Always specify `--env-file .env.docker`; otherwise Compose loads the host `.env`.

## Production

Use `compose.yaml` alone. It builds the application and frontend into images,
uses production dependencies, and caches Laravel configuration at startup.
Keep the generated `APP_KEY` and database passwords stable between deployments.
Set `APP_URL` to the public HTTPS URL. The default web binding is localhost;
terminate HTTPS at the server's reverse proxy, or change `WEB_BIND_ADDRESS`
when the HTTP port needs to be exposed directly.

```sh
docker compose --env-file .env.docker build
docker compose --env-file .env.docker run --rm setup
docker compose --env-file .env.docker up -d --force-recreate --wait
```

On Windows, use `./docker/compose.ps1 -Production` with the same subcommands.
Development and production share the project's data volumes; stop the development
stack before switching. Migrations run only through `setup`, not in every web
or worker container. Run `setup` once per deployment, before restarting workers.
The production worker has a 120-second job timeout, a 180-second retry interval,
and a 150-second shutdown grace period. MySQL is available only on the internal
Docker network. Application logs go to container stderr.

The PHP-FPM master uses the official image's default root account to open its
log stream, while its request workers run as `www-data`. Artisan commands and
the queue worker also run as `www-data`. Use `exec --user www-data app ...`
when running commands that write files in production.

The Docker example uses `CONTACT_PIPELINE=database_only` so local forms work
without external credentials. For Didar, set `CONTACT_PIPELINE=didar_with_database`,
`DIDAR_API_KEY`, and `DIDAR_DEAL_OWNER_USERNAME` in `.env.docker`. Supply your
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
