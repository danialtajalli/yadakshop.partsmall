#!/bin/bash
set -euo pipefail
docker exec partsmall-prod-mysql-1 sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump --single-transaction --quick --no-tablespaces --set-gtid-purged=OFF --routines --events --triggers --hex-blob --user=root --databases "$MYSQL_DATABASE"' | gzip -1
