#!/bin/sh
set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
    echo "[entrypoint] .env missing, copying .env.example..."
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    echo "[entrypoint] generating APP_KEY..."
    php artisan key:generate --ansi
fi

if [ -n "$DB_HOST" ]; then
    echo "[entrypoint] waiting for database at $DB_HOST:${DB_PORT:-3306}..."
    until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
        sleep 1
    done
    echo "[entrypoint] database is up."
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

# Symlink /storage HARUS relatif: symlink absolut buatan host (mis. Herd di macOS)
# menunjuk path yang tidak ada di dalam container -> foto upload 404.
# ln -sfn idempoten: memperbaiki link putus/absolut tanpa menyentuh yang sudah benar.
ln -sfn ../storage/app/public public/storage

exec "$@"
