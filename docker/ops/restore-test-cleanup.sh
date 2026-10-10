#!/bin/bash
set -euo pipefail
test "${RESTORE_VERIFIED:-0}" = 1 || { echo 'Confirm verified off-server backups and successful restore first.' >&2; exit 1; }
container=partsmall-restore-validation-20261010
volume=partsmall-restore-validation-20261010
test "$(docker inspect --format '{{index .Config.Labels "partsmall.purpose"}}' "$container")" = restore-validation
test "$(docker inspect --format '{{.HostConfig.NetworkMode}}' "$container")" = none
test "$(docker inspect --format '{{range .Mounts}}{{if eq .Destination "/var/lib/mysql"}}{{.Name}}{{end}}{{end}}' "$container")" = "$volume"
test "$(docker volume inspect --format '{{index .Labels "partsmall.purpose"}}' "$volume")" = restore-validation
test "$(docker ps -a --filter "volume=$volume" --format '{{.Names}}')" = "$container"
docker stop --timeout 60 "$container"
docker rm "$container"
test -z "$(docker ps -a --filter "volume=$volume" --format '{{.Names}}')"
docker volume rm "$volume"
df -h /
