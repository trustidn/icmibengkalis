#!/usr/bin/env bash
# Rilis produksi di server: build image lokal, jalankan migration, cache config.
# Pakai: ./deploy.sh
set -euo pipefail

if [ ! -f .env.production ]; then
    echo "Error: .env.production tidak ditemukan. Salin dari .env.production.example dan isi nilai produksi." >&2
    exit 1
fi

if [ ! -f auth.json ]; then
    if [ -t 0 ]; then
        echo "Kredensial Flux Pro dibutuhkan untuk mengunduh livewire/flux-pro (composer.fluxui.dev)."
        read -rp "Email akun Flux: " FLUX_EMAIL
        read -rsp "License key (input tersembunyi): " FLUX_KEY; echo
        printf '{\n    "http-basic": {\n        "composer.fluxui.dev": {\n            "username": "%s",\n            "password": "%s"\n        }\n    }\n}\n' "$FLUX_EMAIL" "$FLUX_KEY" > auth.json
        chmod 600 auth.json
        echo "auth.json dibuat (di-gitignore, tidak akan ter-commit)."
    else
        echo "Error: auth.json (kredensial Flux Pro) tidak ditemukan dan sesi non-interaktif." >&2
        echo "Buat manual: composer config --auth http-basic.composer.fluxui.dev <email> <license-key>" >&2
        echo "atau salin dari mesin lain: scp auth.json user@server:$(pwd)/" >&2
        exit 1
    fi
fi

COMPOSE="docker compose --env-file .env.production -f docker-compose.prod.yml"

$COMPOSE build
$COMPOSE up -d
$COMPOSE exec app php artisan migrate --force
$COMPOSE exec app sh -c \
  "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache"

echo "Deploy selesai."
