#!/bin/bash
set -euo pipefail
sql='SELECT "companies",COUNT(*) FROM partsmall.companies UNION ALL SELECT "cars",COUNT(*) FROM partsmall.cars UNION ALL SELECT "models",COUNT(*) FROM partsmall.models UNION ALL SELECT "parts",COUNT(*) FROM partsmall.parts UNION ALL SELECT "shops",COUNT(*) FROM partsmall.shops UNION ALL SELECT "users",COUNT(*) FROM partsmall.users;'
docker exec partsmall-prod-mysql-1 sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B -e "$1"' sh "$sql"
docker exec partsmall-restore-validation-20261010 mysql -uroot -N -B -e "$sql"
docker exec partsmall-restore-validation-20261010 mysql -uroot -N -B -e 'SELECT table_schema,count(*) FROM information_schema.tables WHERE table_schema NOT IN ("mysql","sys","performance_schema","information_schema") GROUP BY table_schema;'
