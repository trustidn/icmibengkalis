#!/bin/sh
set -e

cd /var/www/html

if [ -n "$DB_HOST" ]; then
    echo "[entrypoint] waiting for database at $DB_HOST:${DB_PORT:-3306}..."
    until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
        sleep 1
    done
    echo "[entrypoint] database is up."
fi

exec "$@"
