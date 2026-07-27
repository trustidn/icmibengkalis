#!/usr/bin/env bash
# Rilis produksi di server: build image lokal, jalankan migration, cache config.
# Pakai: ./deploy.sh
set -euo pipefail

if [ ! -f .env.production ]; then
    echo "Error: .env.production tidak ditemukan. Salin dari .env.production.example dan isi nilai produksi." >&2
    exit 1
fi

COMPOSE="docker compose --env-file .env.production -f docker-compose.prod.yml"

$COMPOSE build
$COMPOSE up -d
$COMPOSE exec app php artisan migrate --force
$COMPOSE exec app sh -c \
  "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache"

echo "Deploy selesai."
