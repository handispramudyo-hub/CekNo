# ---------------------------------------------------------------
# Stage 1: dependency build (composer)
# ---------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

RUN php artisan package:discover --ansi

# ---------------------------------------------------------------
# Stage 2: runtime PHP-FPM
# ---------------------------------------------------------------
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip bcmath opcache intl

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

RUN printf '%s\n' \
    'opcache.enable=1' \
    'opcache.memory_consumption=128' \
    'opcache.interned_strings_buffer=16' \
    'opcache.max_accelerated_files=10000' \
    'opcache.revalidate_freq=0' \
    'opcache.validate_timestamps=0' \
    'opcache.save_comments=1' \
    'opcache.fast_shutdown=1' > /usr/local/etc/php/conf.d/opcache.ini

RUN printf '%s\n' \
    '[global]' \
    'daemonize = no' \
    '[www]' \
    'pm.status_path = /status' \
    'pm = dynamic' \
    'pm.max_children = 20' \
    'pm.start_servers = 4' \
    'pm.min_spare_servers = 2' \
    'pm.max_spare_servers = 8' \
    'ping.path = /ping' > /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/backend

COPY --from=vendor /app /var/www/backend

RUN mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000