#!/bin/sh
set -e

mkdir -p /var/www/html/storage/app/public/Stories/Images
mkdir -p /var/www/html/storage/app/public/Stories/Gallery
mkdir -p /var/www/html/storage/app/public/Gallery

# Copy bundled files into Laravel storage, only if they exist
if [ -d /var/www/html/Stories/Images ]; then
    cp -a /var/www/html/Stories/Images/. /var/www/html/storage/app/public/Stories/Images/
fi

if [ -d /var/www/html/Stories/Gallery ]; then
    cp -a /var/www/html/Stories/Gallery/. /var/www/html/storage/app/public/Stories/Gallery/
fi

rm -f /var/www/html/public/storage || true
php artisan storage:link || true

php artisan config:clear || true
php artisan cache:clear || true
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
