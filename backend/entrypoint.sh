#!/bin/sh
set -e

# Named volume bisa membawa data lama ber-owner root; pastikan struktur
# storage selalu siap ditulis worker www-data di setiap start.
rm -rf /var/www/backend/storage/framework/{sessions,views,cache}
mkdir -p \
    /var/www/backend/storage/framework/sessions \
    /var/www/backend/storage/framework/views \
    /var/www/backend/storage/framework/cache \
    /var/www/backend/storage/logs \
    /var/www/backend/bootstrap/cache

chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache
chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache

# Perintah tambahan (mis. `docker compose run backend php artisan ...`)
# harus diteruskan; tanpanya jalankan php-fpm untuk service utama.
if [ -n "$1" ]; then
    exec "$@"
fi

exec php-fpm