FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath opcache intl

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=10000\n\
opcache.revalidate_freq=0\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=1" > /usr/local/etc/php/conf.d/opcache.ini

RUN echo "pm.status_path = /status\n\
pm = dynamic\n\
pm.max_children = 20\n\
pm.start_servers = 4\n\
pm.min_spare_servers = 2\n\
pm.max_spare_servers = 8\n\
ping.path = /ping" > /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/backend

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache

EXPOSE 9000