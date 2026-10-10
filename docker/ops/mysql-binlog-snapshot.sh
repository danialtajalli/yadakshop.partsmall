#!/bin/bash
set -euo pipefail
date -u +%FT%TZ
df -B1 /
docker exec partsmall-prod-mysql-1 sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B -e '\''SHOW BINARY LOGS; SHOW VARIABLES WHERE Variable_name IN ("binlog_expire_logs_seconds","binlog_expire_logs_auto_purge"); SELECT COUNT(*) AS replication_senders FROM information_schema.processlist WHERE COMMAND LIKE "Binlog Dump%";'\'''
