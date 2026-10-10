#!/bin/sh
set -eu
mysql -N -B -e 'SELECT table_schema,engine,count(*) FROM information_schema.tables WHERE table_schema NOT IN ("mysql","sys","performance_schema","information_schema") GROUP BY table_schema,engine; SELECT @@version;'
docker exec partsmall-prod-mysql-1 sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B -e '\''SELECT table_schema,engine,count(*) FROM information_schema.tables WHERE table_schema NOT IN ("mysql","sys","performance_schema","information_schema") GROUP BY table_schema,engine; SELECT @@version;'\'''
df -B1 /
