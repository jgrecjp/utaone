#!/bin/sh
set -eu
mkdir -p /data/media storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
touch /data/utaone.sqlite3
chown -R www-data:www-data /data storage bootstrap/cache
php artisan migrate --force
php artisan config:cache
exec "$@"
