#!/bin/sh
set -eu

mode=${1:-}
case "$mode" in
    dev) default_env=.env.docker; default_project=partsmall-dev; overlay=compose.dev.yaml ;;
    prod) default_env=.env.production; default_project=partsmall-prod; overlay=compose.prod.yaml ;;
    *) printf 'Usage: sh docker/compose.sh dev|prod [Compose arguments]\n' >&2; exit 2 ;;
esac
shift

project_root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
cd "$project_root"
export DOCKER_ENV_FILE=${DOCKER_ENV_FILE:-$default_env}

exec docker compose --env-file "$DOCKER_ENV_FILE" \
    --project-name "${COMPOSE_PROJECT_NAME:-$default_project}" \
    -f compose.yaml -f "$overlay" "$@"
