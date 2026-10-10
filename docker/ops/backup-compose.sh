#!/bin/sh
set -eu
cd /var/www/partsmall-releases/production-20261008
sh docker/compose.sh prod --profile workers config --format json
