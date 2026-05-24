#!/bin/sh
set -e

cd /var/www/html

PORT="${PORT:-8080}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan storage:link 2>/dev/null || true

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

echo "Waiting for database..."
i=0
until php artisan migrate --force; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "Database not ready after 60s"
        exit 1
    fi
    sleep 2
done

php artisan db:seed --force --class=UserSeeder 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
