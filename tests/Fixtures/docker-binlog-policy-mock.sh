#!/bin/bash
set -euo pipefail
if [ "$1" = inspect ]; then
    if [[ "$3" = *Labels* ]]; then
        echo "${MOCK_PROJECT:-partsmall-prod}"
    else
        echo partsmall-prod-mysql
    fi
elif [ "$1" = exec ]; then
    query=${!#}
    case "$query" in
        *'Binlog Dump%'*) echo "${MOCK_REPLICAS:-0}" ;;
        *replication_connection_configuration*|*Repl_slave_priv*) echo 0 ;;
        'SHOW REPLICAS;') ;;
        'SHOW BINARY LOGS;') printf 'binlog.000026\t100\tNo\nbinlog.000027\t100\tNo\n' ;;
        'SET PERSIST'*|'PURGE BINARY LOGS TO'*) printf '%s\n' "$query" >> "$MOCK_MUTATIONS" ;;
        'SHOW GLOBAL VARIABLES'*) echo 'log_bin ON' ;;
        *) echo "Unexpected mock query" >&2; exit 3 ;;
    esac
else
    echo 'Unexpected mock Docker command' >&2
    exit 3
fi
