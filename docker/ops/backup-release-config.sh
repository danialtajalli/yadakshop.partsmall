#!/bin/bash
set -euo pipefail
tar --exclude='*/storage/logs/*' --exclude='*/storage/framework/*' --exclude='*/bootstrap/cache/*' -czf - -C / \
    var/www/partsmall-releases/production-20261008 \
    var/www/partsmall \
    etc/nginx etc/mysql etc/php/8.4/fpm etc/redis \
    etc/logrotate.d etc/systemd/system etc/cron.d etc/crontab
