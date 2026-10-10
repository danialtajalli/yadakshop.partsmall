# Existing Server Cutover

This runbook is for a Linux server with existing NGINX, MySQL, phpMyAdmin,
and a live non-Docker Partsmall installation. Execute one phase at a time.
Do not run a development stack against production data.

## 1. Inspect and Back Up

Before installing anything, record the OS, Docker/Compose versions (if installed),
free disk space, occupied ports, existing Docker/VPN subnets, current MySQL or
MariaDB version, table engines, Partsmall paths, PHP user, queue services, cron
jobs, and NGINX/phpMyAdmin configuration paths.

Take a protected initial backup of the Partsmall database, uploads, the live
`.env` (especially `APP_KEY`), and NGINX configuration. Verify a restore, not just
the existence of a dump. Preserve the original files and services for rollback.
Install supported Docker Engine and the Compose plugin for the detected OS.
Compose must be at least 2.24.4 (`!override` support); use current Docker Engine
(at least 28) for the documented localhost-published-port isolation.

The image currently defaults to MySQL 8.4. Do not turn a runtime migration into
an untested database upgrade. Set `MYSQL_VERSION` to the validated target version.
MariaDB dumps, older MySQL schemas, collations, definers, routines, and events
need a trial restore and explicit compatibility checks.

## 2. Prepare the Production Release

Upload a separate release directory; do not overwrite the serving installation.
Anchor copy exclusions to the repository root (`/vendor`, `/node_modules`) so
application overrides in `resources/views/vendor` and editor assets in
`public/vendor` are included in the release.
Create `.env.production` from `.env.production.example`, set permissions to 600,
and populate it before building. Preserve the live `APP_KEY`, public HTTPS
`APP_URL`, mail settings, Didar/contact pipeline, captcha keys, and other live
integration settings. Generate new database passwords for the new Docker
database. Never publish `docker compose config` output: it contains secrets.

Alternatively, if the host has PHP and the project's Composer dependencies,
prepare the file from the live `.env` without editing it. The third argument
is the reviewed target MySQL version, not an automatic upgrade:

```sh
php docker/init-production-env.php .env .env.production 8.0
```

This preserves the source text and all non-container settings, appends scoped
Docker overrides, generates separate database passwords, and restricts the new
file to mode 600 before writing credentials. It refuses to overwrite any existing
target. Validate the resulting configuration without printing it:

```sh
sh docker/compose.sh prod --profile workers config --quiet
```

When the live queue uses Redis, a MySQL dump does not include pending jobs.
Identify only Partsmall's queues and workers, drain ready/reserved/delayed jobs
or arrange a tested transfer before switching to dedicated Docker Redis queues. Do not flush
shared Redis or stop workers belonging to other applications.

The examples assume these reviewed values:

- Host NGINX upstream: `127.0.0.1:8000` (`WEB_PORT`).
- Host phpMyAdmin's additional database: `127.0.0.1:3307` (`MYSQL_ADMIN_PORT`).
- Dedicated Docker subnet: `172.30.50.0/24` (`DOCKER_SUBNET`). Check for conflicts.
- Production volume prefix: `partsmall-prod` (`PRODUCTION_VOLUME_PREFIX`).

Production pins the web/SQL listeners to localhost, independently of development
binding settings. PHP-FPM is not published. Laravel trusts forwarded scheme,
port, and client-IP headers only from the dedicated Docker subnet, not the
Internet. NGINX remains responsible for TLS and all other sites.

Create the external production volumes. They survive container recreation and
Compose `down --volumes`; `docker volume rm` can still delete them. They are not
backups. Use the actual prefix if changed; never remove existing volumes blindly.

```sh
docker volume create partsmall-prod-mysql
docker volume create partsmall-prod-storage
docker volume create partsmall-prod-uploads
docker volume create partsmall-prod-redis-queue
sh docker/compose.sh prod build
```

Rehearse before interrupting the live site. Use a separate `.env.rehearsal` with
`PRODUCTION_VOLUME_PREFIX=partsmall-rehearsal`, `WEB_PORT=8001`,
`MYSQL_ADMIN_PORT=3308`, and a different nonconflicting `DOCKER_SUBNET`.
Create the four `partsmall-rehearsal-*` volumes, including `redis-queue`, and use
`DOCKER_ENV_FILE=.env.rehearsal COMPOSE_PROJECT_NAME=partsmall-rehearsal sh docker/compose.sh prod ...`.
Restore the initial backup there before `run --rm setup`. Do not start workers,
and disable outbound mail/integrations for rehearsal. Keep production volumes
fresh for the final restore. Review application behavior, database row counts,
uploads, login, and migration SQL/results. Secure cookies require an HTTPS test
hostname (or SSH tunnel to a properly configured HTTPS proxy) for login testing.

## 3. Freeze the Old Installation

Serve maintenance responses only for Partsmall's public and admin endpoints.
Do not take down the host NGINX, other websites, host MySQL, or phpMyAdmin.
Do not start routing to Docker yet.

Stop the old Partsmall queue consumers, scheduler/cron tasks, and any external
writers. Let in-flight requests/jobs finish before the final backup. Maintenance
mode alone does not guarantee workers or external integrations stop writing.
Restrict admin/phpMyAdmin writes to the source database during the freeze.

## 4. Take the Final Backup

After the write freeze, take the final database dump and upload archive. For
MySQL/InnoDB, the shape is below; replace the capitalized identifiers and choose
flags supported by the actual source client/version. Nontransactional tables
require locks or another consistent backup approach. No schema changes should
occur during the dump. Check exit status, size, and restore results.

```sh
umask 077
mysqldump --single-transaction --quick --no-tablespaces \
  --routines --events --triggers --hex-blob \
  -u BACKUP_USER -p DATABASE_NAME > /protected/backups/partsmall-final.sql
```

Dump only Partsmall's application database, not MySQL system schemas/users.
Review GTID handling, definers, and scheduled events for the source/target.
Preserve valid production jobs unless deliberately drained; never let rehearsal
jobs send emails or call production integrations.

## 5. Restore Into Docker

The following assumes fresh production database/upload volumes. If they contain
rehearsal or other data, stop and review instead of importing over them.
Run from the new release directory after checking `.env.production`.

```sh
sh docker/compose.sh prod up -d --wait mysql
sh docker/compose.sh prod exec -T mysql sh -c \
  'MYSQL_PWD="$MYSQL_PASSWORD" exec mysql --user="$MYSQL_USER" "$MYSQL_DATABASE"' \
  < /protected/backups/partsmall-final.sql
sh docker/compose.sh prod run --rm setup
```

Fix restore errors before proceeding; routines/definers can need a reviewed
administrative restore. `setup` runs migrations once and never `migrate:fresh`.
Do not run old and new queue workers together during migration.

Import the final upload directory using a host path, not a MySQL data directory.
Set `UPLOAD_SOURCE` to the real legacy upload directory in `.env.production`.
The importer copies missing files and repairs target ownership, without changing
the source. Restoring into a fresh upload volume prevents stale files being kept.
Also copy any application-required private storage after reviewing its contents;
do not copy stale compiled views/config or `public/hot`.

```sh
sh docker/compose.sh prod run --rm import-uploads
sh docker/compose.sh prod up -d --wait app web
```

The queue has the `workers` profile and is deliberately absent at this stage.
Verify `http://127.0.0.1:8000/up`, row counts, uploads, and application logs.
Perform HTTPS login/admin/form tests through a temporary protected proxy if
needed. Web health checks may enqueue request logs; this is not a reason to
enable all production workers early.

## 6. Add Docker MySQL to Existing phpMyAdmin

Keep its current server entry. Append the fragment from
`docker/phpmyadmin-server.php.example` using the actual configuration layout.
If pasting inside an existing PHP configuration, omit the snippet's opening
`<?php` tag. If the installation supports included configuration files, use
the snippet as a separate included PHP file instead.
It adds a server named `Partsmall Docker`, with `127.0.0.1`, TCP port `3307`,
and cookie authentication. Adjust the port to `MYSQL_ADMIN_PORT` if changed.
Use the Docker application database account, not the host's existing MySQL
password. Do not store credentials in the snippet or enable remote root access.
Use `127.0.0.1`, not `localhost`, which may select a Unix socket and ignore the
port. The host MySQL remains on its current port. Retain phpMyAdmin's existing
authentication/access restrictions; a localhost SQL port does not secure a
publicly exposed phpMyAdmin web interface.

This assumes phpMyAdmin runs on the host. If it is containerized or remote,
inspect its network first; its `127.0.0.1` is not the Docker host.

## 7. Switch Only Partsmall's NGINX Routing

Back up its vhost configuration. Adapt `docker/nginx-host-location.conf.example`
inside the existing Partsmall HTTPS server block, preserving certificates and
HTTP-to-HTTPS redirects. Keep maintenance active while editing. Remove or adjust
old Partsmall PHP/static/alias locations that could bypass the new proxy, but
preserve unrelated sites and any separate phpMyAdmin routes. Match the upstream
port to `WEB_PORT`. If there is a CDN/front proxy, review NGINX real-IP handling
instead of trusting arbitrary incoming forwarding headers.

```sh
sudo nginx -t
sudo systemctl reload nginx
```

Do not reload if validation fails. Verify the public domain's HTTPS redirects,
static assets, existing upload URLs, client IPs, admin login, CSRF, and forms.
Only after validation remove maintenance and enable the new workers:

```sh
sh docker/compose.sh prod --profile workers up -d --wait
sh docker/compose.sh prod logs --tail 100 app web queue
```

There are currently no scheduled application tasks in `routes/console.php`.
Review actual server cron entries and integrations before retiring the old ones.

## Rollback and Later Deployments

Keep old code, DB backups, uploads, and vhost configuration. Before new production
writes, reverting the vhost and old workers is straightforward if the old database
has remained unchanged. After new writes, simply reverting to the old database
loses those writes; freeze traffic and reconcile/restore a reviewed database first.
Do not assume schema migrations are compatible with old application code.

For later deployments, back up, review migrations, use a maintenance window when
needed, stop workers, build images, run setup, then recreate services/workers.
Restarting queue workers alone does not swap their container image.

```sh
sh docker/compose.sh prod --profile workers stop queue
sh docker/compose.sh prod build
sh docker/compose.sh prod run --rm setup
sh docker/compose.sh prod --profile workers up -d --force-recreate --wait
```

Keep `.env.production` stable and protected. Back up database and uploads off the
server on a schedule and test restoration. Never treat volumes as backups.
