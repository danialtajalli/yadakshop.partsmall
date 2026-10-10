#!/bin/bash
set -euo pipefail
docker exec partsmall-prod-app-1 tar -czf - -C /var/www/html/storage app
