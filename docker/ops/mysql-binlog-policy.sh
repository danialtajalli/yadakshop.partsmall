#!/bin/bash
set -euo pipefail

mode=${1:-check}
case "$mode" in
    check|apply|purge) ;;
    *) echo 'Usage: bash docker/ops/mysql-binlog-policy.sh check|apply|purge [archived-cutoff]' >&2; exit 2 ;;
esac

container=partsmall-prod-mysql-1
sql() {
    docker exec "$container" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B -e "$1"' sh "$1"
}

test "$(docker inspect --format '{{index .Config.Labels "com.docker.compose.project"}}' "$container")" = partsmall-prod
test "$(docker inspect --format '{{range .Mounts}}{{if eq .Destination "/var/lib/mysql"}}{{.Name}}{{end}}{{end}}' "$container")" = partsmall-prod-mysql

if [ "$mode" != check ]; then
    for flag in BACKUP_VERIFIED RESTORE_VERIFIED RECOVERY_REVIEWED; do
        if [ "${!flag:-0}" != 1 ]; then
            printf 'Refusing changes: %s must be explicitly confirmed.\n' "$flag" >&2
            exit 1
        fi
    done
    # A disconnected replica can still need old logs; the operator must review it.
    test "$(sql 'SELECT COUNT(*) FROM information_schema.processlist WHERE COMMAND LIKE "Binlog Dump%";')" = 0
    test "$(sql 'SELECT COUNT(*) FROM performance_schema.replication_connection_configuration;')" = 0
    test "$(sql 'SELECT COUNT(*) FROM mysql.user WHERE Repl_slave_priv="Y" AND User NOT IN ("root","mysql.infoschema","mysql.session","mysql.sys");')" = 0
    test -z "$(sql 'SHOW REPLICAS;')"
fi

if [ "$mode" = apply ]; then
    retention=${MYSQL_BINLOG_EXPIRE_LOGS_SECONDS:-86400}
    size=${MYSQL_MAX_BINLOG_SIZE:-67108864}
    compression=${MYSQL_BINLOG_TRANSACTION_COMPRESSION:-ON}
    [[ "$compression" = ON || "$compression" = OFF ]] || exit 2
    [[ "$retention" =~ ^[1-9][0-9]{0,7}$ && "$size" =~ ^[1-9][0-9]{0,9}$ ]] || exit 2
    test "$retention" -ge 3600
    test "$retention" -le 2592000
    test "$size" -ge 4096
    test "$size" -le 1073741824
    test "$((size % 4096))" = 0
    sql "SET PERSIST max_binlog_size=$size; SET PERSIST binlog_transaction_compression=$compression; SET PERSIST binlog_expire_logs_seconds=$retention; SET PERSIST binlog_expire_logs_auto_purge=ON;"
elif [ "$mode" = purge ]; then
    cutoff=${2:-}
    [[ "$cutoff" =~ ^binlog\.[0-9]+$ ]] || { echo 'Supply the exact archived cutoff log.' >&2; exit 2; }
    test "${BINLOG_ARCHIVE_VERIFIED:-0}" = 1 || { echo 'Verify every binlog below the cutoff in an off-server archive first.' >&2; exit 1; }
    logs=$(sql 'SHOW BINARY LOGS;')
    printf '%s\n' "$logs" | awk -v cutoff="$cutoff" '$1==cutoff {found=1} END {exit !found}'
    # MySQL updates its index and never purges the named cutoff itself.
    sql "PURGE BINARY LOGS TO '$cutoff'; SHOW WARNINGS;"
fi

sql 'SHOW GLOBAL VARIABLES WHERE Variable_name IN ("log_bin","binlog_expire_logs_seconds","binlog_expire_logs_auto_purge","max_binlog_size","binlog_transaction_compression"); SELECT VARIABLE_NAME,VARIABLE_VALUE FROM performance_schema.persisted_variables WHERE VARIABLE_NAME IN ("binlog_expire_logs_seconds","binlog_expire_logs_auto_purge","max_binlog_size","binlog_transaction_compression");'
sql 'SHOW BINARY LOGS;' | awk '{count++;bytes+=$2} END {printf "binlog_count=%d total_bytes=%.0f\n",count,bytes}'
df -h /
