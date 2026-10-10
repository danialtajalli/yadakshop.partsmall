#!/bin/bash
set -euo pipefail
mapfile -t partsmall_host_databases < <(mysql -N -B -e 'SELECT SCHEMA_NAME FROM information_schema.schemata WHERE SCHEMA_NAME NOT IN ("mysql","sys","performance_schema","information_schema")')
test "${#partsmall_host_databases[@]}" -gt 0
mysqldump --single-transaction --quick --no-tablespaces --set-gtid-purged=OFF --routines --events --triggers --hex-blob --databases "${partsmall_host_databases[@]}" | gzip -1
