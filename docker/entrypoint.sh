#!/bin/sh
set -eu

mkdir -p storage/app/private storage/app/public storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs \
    bootstrap/cache public/panel/assets/uploads
chown www-data:www-data storage storage/app storage/app/private storage/app/public \
    storage/framework storage/framework/cache storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs \
    bootstrap/cache public/panel/assets/uploads

if [ "${APP_ENV:-production}" = "production" ]; then
    gosu www-data php artisan config:cache
fi

if [ "$1" = "php-fpm" ]; then
    exec "$@"
fi

exec gosu www-data "$@"
