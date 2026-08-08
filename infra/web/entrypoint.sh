#!/bin/sh
set -eu

mkdir -p /data /var/www/html/storage/framework/cache/data /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs
touch /data/database.sqlite
chown -R www-data:www-data /data /var/www/html/storage /var/www/html/bootstrap/cache

php artisan migrate --force
php artisan config:cache
php artisan view:cache

exec "$@"
