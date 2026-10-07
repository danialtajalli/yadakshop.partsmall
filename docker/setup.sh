#!/bin/sh
set -eu

if [ "${APP_ENV:-production}" = "local" ]; then
    export COMPOSER_HOME=/tmp/composer
    export GIT_CONFIG_COUNT=1
    export GIT_CONFIG_KEY_0=safe.directory
    export GIT_CONFIG_VALUE_0=/var/www/html
    composer install --prefer-dist --no-interaction
    php artisan config:clear
fi

php artisan migrate --force
