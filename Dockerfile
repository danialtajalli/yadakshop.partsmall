# syntax=docker/dockerfile:1
ARG PHP_VERSION=8.4
ARG NODE_VERSION=24

FROM php:${PHP_VERSION}-fpm-bookworm AS php-base
ARG PHPREDIS_VERSION=6.3.0
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        gosu git unzip libicu-dev libonig-dev libzip-dev libpng-dev \
        libjpeg62-turbo-dev libfreetype6-dev libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath gd intl mbstring opcache pcntl pdo_mysql pdo_sqlite zip \
    && pecl install redis-${PHPREDIS_VERSION} \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY docker/php.ini /usr/local/etc/php/conf.d/partsmall.ini
COPY docker/entrypoint.sh /usr/local/bin/partsmall-entrypoint
RUN chmod +x /usr/local/bin/partsmall-entrypoint
ENTRYPOINT ["partsmall-entrypoint"]
CMD ["php-fpm", "-F"]

FROM php-base AS production-dependencies
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-autoloader

FROM production-dependencies AS development-dependencies
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --prefer-dist --no-interaction --no-scripts --no-autoloader

FROM node:${NODE_VERSION}-bookworm-slim AS frontend
WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY --from=production-dependencies /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

FROM php-base AS development
COPY --chown=www-data:www-data --from=development-dependencies /var/www/html/vendor ./vendor
COPY . .
RUN mkdir -p storage/app/private storage/app/public storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs public/panel/assets/uploads bootstrap/cache \
    && test -f resources/views/vendor/pagination/tailwind.blade.php \
    && test -f public/vendor/tinymce/tinymce.min.js \
    && composer dump-autoload --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && php artisan filament:assets \
    && chown -R www-data:www-data storage bootstrap/cache

FROM php-base AS production
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
COPY --from=production-dependencies /var/www/html/vendor ./vendor
COPY . .
COPY --from=frontend /var/www/html/public/build ./public/build
RUN mkdir -p storage/app/private storage/app/public storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs public/panel/assets/uploads bootstrap/cache \
    && test -f resources/views/vendor/pagination/tailwind.blade.php \
    && test -f public/vendor/tinymce/tinymce.min.js \
    && composer dump-autoload --no-dev --classmap-authoritative --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && php artisan filament:assets \
    && chown -R www-data:www-data storage bootstrap/cache

FROM nginx:1.28-alpine AS web
WORKDIR /var/www/html
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public ./public
RUN mkdir -p storage/app/public
