#!/bin/sh
# Read-only inventory. Run as root; never prints environment files or credentials.
set -u
date -u +%FT%TZ
df -B1 /
df -i /
free -m
ss -lntp
docker ps -a --format '{{.Names}} | {{.Image}} | {{.Status}} | {{.Ports}}'
docker system df
docker inspect --format '{{.Name}} image={{.Image}} workdir={{index .Config.Labels "com.docker.compose.project.working_dir"}} restart={{.HostConfig.RestartPolicy.Name}} log={{json .HostConfig.LogConfig}} mounts={{json .Mounts}}' $(docker ps -aq)
du -sh /var/log /var/backups /var/www/partsmall /var/www/partsmall-releases /var/lib/docker/volumes/* 2>/dev/null
journalctl --disk-usage
nginx -t
nginx -T 2>/dev/null | awk '$1 == "server_name" || $1 == "root" || $1 == "proxy_pass" || $1 == "fastcgi_pass" { print }'
systemctl is-enabled docker nginx mysql redis-server 2>/dev/null
systemctl is-active docker nginx mysql redis-server 2>/dev/null
mysql --batch --skip-column-names -e 'SELECT table_schema, COUNT(*), ROUND(SUM(data_length+index_length)/1048576,1) FROM information_schema.tables GROUP BY table_schema; SHOW VARIABLES WHERE Variable_name IN ("version", "log_bin", "binlog_expire_logs_seconds", "max_connections");' 2>/dev/null
docker exec partsmall-prod-mysql-1 sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B -e '\''SHOW VARIABLES WHERE Variable_name IN ("version", "log_bin", "binlog_expire_logs_seconds", "max_connections"); SHOW BINARY LOGS; SELECT COUNT(*) AS active_transactions, COALESCE(MAX(TIMESTAMPDIFF(SECOND,trx_started,NOW())),0) AS oldest_transaction_seconds FROM information_schema.innodb_trx; SELECT COMMAND,COUNT(*),MAX(TIME) FROM information_schema.processlist GROUP BY COMMAND;'\'''
find /var/backups /home/ubuntu -maxdepth 2 -type f \( -name '*.sql' -o -name '*.sql.gz' -o -name '*backup*.tar*' \) -printf '%p %s bytes\n' 2>/dev/null
