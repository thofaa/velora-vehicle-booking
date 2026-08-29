#!/bin/sh
set -e

# ROLE=web -> layani permintaan HTTP (Nginx), tanpa persiapan Laravel.
if [ "$ROLE" = "web" ]; then
    exec nginx -g 'daemon off;'
fi

# ROLE lain (app/service) -> siapkan aplikasi lalu jalankan PHP-FPM.
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views
chmod -R 777 storage bootstrap/cache
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
php artisan package:discover --ansi
php artisan key:generate --force >/dev/null 2>&1 || true

php artisan migrate --force

if [ "$(php artisan tinker --execute 'echo App\Models\User::count();' 2>/dev/null)" = "0" ]; then
    php artisan db:seed --force
fi

exec php-fpm