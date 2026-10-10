#!/bin/bash
set -euo pipefail
container=partsmall-restore-validation-20261010
volume=partsmall-restore-validation-20261010
if docker container inspect "$container" >/dev/null 2>&1 || docker volume inspect "$volume" >/dev/null 2>&1; then
    printf 'Restore-test target already exists; refusing to reuse it.\n' >&2
    exit 1
fi
test "$(df -B1 --output=avail / | tail -1)" -gt 3221225472
docker volume create --label partsmall.purpose=restore-validation "$volume"
docker run -d --name "$container" --network none --memory 768m \
    --label partsmall.purpose=restore-validation \
    --mount "type=volume,src=$volume,dst=/var/lib/mysql" \
    --env MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    mysql:8.0 --skip-log-bin --event-scheduler=OFF --innodb-buffer-pool-size=128M
for attempt in $(seq 1 60); do
    if docker exec "$container" mysqladmin -uroot ping --silent >/dev/null 2>&1; then
        docker exec "$container" mysql -uroot -N -B -e 'SELECT @@version,@@log_bin,@@event_scheduler;'
        exit 0
    fi
    sleep 1
done
printf 'Restore-test database did not become ready.\n' >&2
exit 1
