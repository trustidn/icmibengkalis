SHELL := /bin/sh

DEV  := docker compose
PROD := docker compose --env-file .env.production -f docker-compose.prod.yml

.DEFAULT_GOAL := help

.PHONY: help \
	env up down restart build ps logs shell db-shell redis-cli \
	install key fresh migrate seed test pint npm artisan composer \
	guard-prod-env prod-env prod-key prod-install prod-seed-rbac \
	prod-build prod-up prod-down prod-restart prod-logs prod-ps prod-shell \
	prod-migrate prod-artisan prod-cache prod-cache-clear \
	prod-db-shell prod-db-backup prod-deploy prod-release-check

## ---- Development -----------------------------------------------------

help: ## Tampilkan daftar perintah
	@grep -E '^[a-zA-Z0-9_-]+:.*## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "}; {printf "  \033[36m%-22s\033[0m %s\n", $$1, $$2}'

env: ## Salin .env.example -> .env (bila belum ada; tidak menimpa)
	@if [ -f .env ]; then echo ".env sudah ada — tidak ditimpa."; else cp .env.example .env && echo "Dibuat: .env"; fi

up: ## Nyalakan seluruh container dev (build bila perlu)
	$(DEV) up -d --build

down: ## Matikan seluruh container dev
	$(DEV) down

restart: down up ## Restart seluruh container dev

build: ## Build ulang image dev tanpa cache
	$(DEV) build --no-cache

ps: ## Status container dev
	$(DEV) ps

logs: ## Ikuti log seluruh container dev (make logs s=app untuk satu servis)
	$(DEV) logs -f $(s)

shell: ## Masuk shell container app (dev)
	$(DEV) exec app sh

db-shell: ## Masuk MySQL client di container db (dev)
	$(DEV) exec db mariadb -u icmi -psecret icmi

redis-cli: ## Buka redis-cli (dev)
	$(DEV) exec redis redis-cli

install: env up ## Instalasi awal dev: .env, up, dependency, key, migrate+seed
	$(DEV) exec app composer install
	$(DEV) exec app php artisan key:generate
	$(DEV) exec app php artisan migrate --seed
	@echo "Selesai. App: http://localhost:8000  Vite: http://localhost:5173  Mailpit: http://localhost:8025"

key: ## Generate APP_KEY (dev)
	$(DEV) exec app php artisan key:generate

fresh: ## migrate:fresh --seed (dev, HAPUS semua data)
	$(DEV) exec app php artisan migrate:fresh --seed

migrate: ## Jalankan migration (dev)
	$(DEV) exec app php artisan migrate

seed: ## Jalankan seeder (dev)
	$(DEV) exec app php artisan db:seed

test: ## Jalankan test suite (dev)
	$(DEV) exec app php artisan test

pint: ## Format kode dengan Pint (dev)
	$(DEV) exec app vendor/bin/pint

npm: ## make npm cmd="run build" (dev)
	$(DEV) exec vite npm $(cmd)

artisan: ## make artisan cmd="route:list" (dev)
	$(DEV) exec app php artisan $(cmd)

composer: ## make composer cmd="require pkg/name" (dev)
	$(DEV) exec app composer $(cmd)

## ---- Production --------------------------------------------------------

# Guard internal (tanpa ##): dipakai target produksi yang butuh .env.production.
guard-prod-env:
	@test -f .env.production || { echo "Error: .env.production belum ada. Jalankan: make prod-env"; exit 1; }

# Guard internal: build image produksi butuh kredensial Flux Pro (auth.json, di-gitignore).
guard-auth-json:
	@test -f auth.json || { echo "Error: auth.json (kredensial Flux Pro) tidak ditemukan — composer install akan gagal 401."; \
		echo "Salin dari mesin dev: scp auth.json user@server:$$(pwd)/"; exit 1; }

prod-env: ## Salin .env.production.example -> .env.production (bila belum ada)
	@if [ -f .env.production ]; then \
		echo ".env.production sudah ada — tidak ditimpa."; \
	else \
		cp .env.production.example .env.production && \
		echo "Dibuat: .env.production" && \
		echo "WAJIB edit: DB_PASSWORD, DB_ROOT_PASSWORD, APP_URL, MAIL_* — lalu jalankan: make prod-install" && \
		echo "(APP_KEY akan diisi otomatis oleh prod-install / make prod-key)"; \
	fi

prod-key: guard-prod-env ## Generate & isi APP_KEY di .env.production (hanya bila masih kosong)
	@if grep -qE '^APP_KEY=.+$$' .env.production; then \
		echo "APP_KEY sudah terisi — tidak diubah."; \
	else \
		KEY="base64:$$(docker run --rm php:8.3-cli php -r 'echo base64_encode(random_bytes(32));')" && \
		sed -i.bak "s|^APP_KEY=.*|APP_KEY=$$KEY|" .env.production && rm -f .env.production.bak && \
		echo "APP_KEY dibuat & disimpan di .env.production."; \
	fi

prod-install: guard-prod-env guard-auth-json prod-key ## Instalasi awal produksi: build, up, migrate, cache, seed RBAC
	@if grep -qE '^DB_PASSWORD=change-me$$' .env.production; then \
		echo "Error: DB_PASSWORD masih nilai contoh ('change-me') — edit .env.production dulu."; exit 1; \
	fi
	$(PROD) build
	$(PROD) up -d
	$(PROD) exec app php artisan migrate --force
	$(PROD) exec app sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache"
	$(PROD) exec app php artisan db:seed --class=RolePermissionSeeder --force
	@echo "Instalasi produksi selesai. Langkah berikutnya: buat akun Super Admin (lihat docs/10-panduan-teknis.md) dan aktifkan 2FA."

prod-seed-rbac: ## Jalankan RolePermissionSeeder produksi (idempoten; ulangi setelah menambah permission)
	$(PROD) exec app php artisan db:seed --class=RolePermissionSeeder --force

prod-build: guard-auth-json ## Build image produksi (target prod)
	$(PROD) build

prod-up: ## Nyalakan stack produksi
	$(PROD) up -d

prod-down: ## Matikan stack produksi
	$(PROD) down

prod-restart: ## Restart stack produksi
	$(PROD) restart

prod-logs: ## Ikuti log produksi (make prod-logs s=app)
	$(PROD) logs -f $(s)

prod-ps: ## Status container produksi
	$(PROD) ps

prod-shell: ## Masuk shell container app (produksi)
	$(PROD) exec app sh

prod-migrate: ## Jalankan migration produksi (--force)
	$(PROD) exec app php artisan migrate --force

prod-artisan: ## make prod-artisan cmd="cache:clear"
	$(PROD) exec app php artisan $(cmd)

prod-cache: ## Bangun ulang cache config/route/view/event produksi
	$(PROD) exec app sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache"

prod-cache-clear: ## Bersihkan seluruh cache produksi (optimize:clear)
	$(PROD) exec app php artisan optimize:clear

prod-db-shell: guard-prod-env ## Masuk MariaDB client di container db produksi
	$(PROD) exec db sh -c 'mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"'

prod-db-backup: guard-prod-env ## Dump database produksi -> backups/icmi-<timestamp>.sql.gz
	@mkdir -p backups
	@FILE="backups/icmi-$$(date +%Y%m%d-%H%M%S).sql.gz" && \
	$(PROD) exec -T db sh -c 'mariadb-dump -u root -p"$$MARIADB_ROOT_PASSWORD" --single-transaction "$$MARIADB_DATABASE"' | gzip > "$$FILE" && \
	echo "Backup tersimpan: $$FILE"

prod-deploy: guard-auth-json ## Build, up, migrate, cache config (rilis penuh)
	$(PROD) pull --ignore-pull-failures || true
	$(PROD) build
	$(PROD) up -d
	$(PROD) exec app php artisan migrate --force
	$(PROD) exec app sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache"
	@echo "Deploy selesai."
