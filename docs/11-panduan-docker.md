# 11. Panduan Menjalankan Docker dari Awal

Panduan praktis langkah-demi-langkah. Untuk rancangan arsitektur & topologi, lihat [docs/09-deployment-docker.md](09-deployment-docker.md). Semua perintah dijalankan dari root proyek.

## 11.1 Prasyarat

- Docker Desktop (atau Docker Engine + Compose plugin) terpasang — `docker --version` dan `docker compose version` harus jalan.
- Tidak perlu PHP/Composer/Node terpasang di host — semuanya jalan di container.
- Port yang dipakai di host (pastikan kosong): **8000** (app), **5173** (Vite), **8025**/**1025** (Mailpit), **3306** (MariaDB), **8080** (phpMyAdmin).

## 11.2 Development

### 11.2.1 Setup awal (sekali saja)

```bash
git clone <url-repo> icmibengkalis   # kalau belum ada
cd icmibengkalis
make install
```

`make install` melakukan semuanya: salin `.env` dari `.env.example` (bila belum ada), build image, nyalakan seluruh container, `composer install`, `key:generate`, `migrate --seed`. Proses pertama kali agak lama (build image PHP + download dependency) — normal.

Setelah selesai, akan tampil ringkasan URL. Buka:

| Servis | URL | Keterangan |
|---|---|---|
| Aplikasi | http://localhost:8000 | via nginx → php-fpm |
| Vite (HMR) | http://localhost:5173 | asset dev, auto-reload |
| Mailpit | http://localhost:8025 | tangkap email lokal (SMTP di port 1025) |
| MariaDB | localhost:3306 | user `icmi` / password `secret` / db `icmi` |
| phpMyAdmin | http://localhost:8080 | kelola database via browser (auto-login di dev) |

Akun demo (lihat [PROGRESS.md](../PROGRESS.md)): `superadmin@demo.test` / `password`.

> Catatan: container dev memakai **MariaDB + Redis** (bukan SQLite seperti setup non-Docker/Herd). Variabel `DB_*`/`REDIS_*`/`SESSION_DRIVER`/`CACHE_STORE`/`QUEUE_CONNECTION` di-override lewat `environment:` di `docker-compose.yml` — file `.env` di host tidak diubah, jadi dev non-Docker (Herd) tetap bisa jalan berdampingan.

> Catatan koneksi: seeder demo (anggota, artikel, galeri) mengunduh foto contoh dari layanan publik (randomuser.me, picsum.photos) saat `migrate --seed`. Tanpa koneksi internet seeding **tetap berhasil** (unduhan yang gagal dilewati diam-diam), hanya saja foto demo kosong/placeholder. Jalankan ulang `make fresh` saat online bila ingin foto demo lengkap.

### 11.2.2 Perintah harian

```bash
make up              # nyalakan seluruh container (build ulang bila ada perubahan Dockerfile)
make down             # matikan seluruh container
make restart          # down lalu up
make build             # build ulang image dev tanpa cache (bila Dockerfile berubah drastis)
make ps               # status container
make logs s=app        # ikuti log satu servis (kosongkan s= untuk semua)
make shell             # masuk shell container app
make db-shell           # masuk MySQL client di container db
make redis-cli          # buka redis-cli
```

### 11.2.3 Perintah Laravel/Composer/NPM

```bash
make artisan cmd="route:list"
make artisan cmd="make:livewire Admin/Contoh"
make composer cmd="require pkg/nama"
make npm cmd="run build"
make migrate           # migrate biasa
make fresh              # migrate:fresh --seed (HAPUS semua data!)
make seed
make test               # php artisan test
make pint               # format kode (wajib bersih sebelum commit)
```

### 11.2.4 Reset total (mulai dari nol lagi)

```bash
make down
docker compose down -v   # tambahan -v: hapus juga volume (data DB, vendor, node_modules)
make install
```

### 11.2.5 Troubleshooting dev

- **Container `app` terus `unhealthy` / `queue` tidak kunjung start**: cek `make logs s=app` — biasanya masih proses `composer install` pertama kali (volume `vendor` kosong). Tunggu sampai muncul `NOTICE: fpm is running`.
- **Port bentrok** (`port is already allocated`): matikan servis lain yang memakai port 8000/5173/8025/3306, atau ubah mapping port di `docker-compose.yml` (kolom kiri pada `ports:`).
- **Perubahan `composer.json`/`package.json` tidak kebaca**: volume `vendor`/`node_modules` sudah terisi versi lama — jalankan `make composer cmd=install` atau `make npm cmd=install`, atau reset total (11.2.4).
- **Assets Vite tidak update**: pastikan servis `vite` jalan (`make ps`) dan browser mengakses lewat `http://localhost:8000` (bukan langsung 5173) supaya `@vite` directive terhubung ke HMR.
- **Foto/file upload tidak tampil (404/403 di URL `/storage/...`)**: symlink `public/storage` buatan host (mis. `php artisan storage:link` via Herd di macOS) menunjuk path ABSOLUT host yang tidak ada di dalam container. Entrypoint dev kini otomatis memperbaikinya jadi relatif (`ln -sfn ../storage/app/public public/storage`) di tiap start container — bila masih terjadi, jalankan `make restart` (perubahan symlink di host kadang tidak langsung terpropagasi ke bind mount macOS/VirtioFS sampai container di-restart; entrypoint baru butuh `make up` sekali untuk rebuild image).

## 11.3 Production

Dijalankan langsung di VPS lewat `docker compose`, tanpa registry eksternal (image di-build lokal di server). Untuk pipeline CI/registry, lihat catatan di [docs/09 §9.5](09-deployment-docker.md#95-alur-rilis-cicd) — `docker-compose.prod.yml` sudah kompatibel (`APP_IMAGE=ghcr.io/... make prod-build` dsb.) bila nanti ingin upgrade ke image dari CI.

### 11.3.1 Instalasi awal di server

```bash
git clone <url-repo> icmibengkalis
cd icmibengkalis
make prod-env          # salin .env.production dari template (tidak menimpa yang sudah ada)
nano .env.production   # WAJIB isi: DB_PASSWORD, DB_ROOT_PASSWORD, APP_URL, MAIL_*,
                        # dan nilai lain sesuai domain produksi (APP_KEY BOLEH dibiarkan kosong)
make prod-install
```

**Kredensial Flux Pro**: `livewire/flux-pro` butuh login `composer.fluxui.dev`. Saat pertama kali `make prod-install`/`prod-build`/`prod-deploy` atau `./deploy.sh` dijalankan, bila `auth.json` belum ada Anda akan **ditanya interaktif** (email akun Flux + license key, input key tersembunyi) — file `auth.json` dibuat otomatis (chmod 600, di-gitignore). Kredensial disuplai ke build image sebagai **BuildKit secret** (`build.secrets` di compose), TIDAK tersimpan di layer image. Alternatif tanpa prompt: salin dari mesin lain (`scp auth.json user@server:/path/repo/`) atau `composer config --auth http-basic.composer.fluxui.dev <email> <license-key>`. Sesi non-interaktif (CI) tanpa `auth.json` gagal dengan pesan jelas.

`make prod-install` melakukan semuanya: isi `APP_KEY` otomatis bila masih kosong (via `make prod-key`, memakai container `php:8.3-cli` — tanpa perlu PHP di host), menolak jalan bila `DB_PASSWORD` masih nilai contoh, lalu build image → up → `migrate --force` → cache config/route/view/event → `db:seed --force` (data fondasi: RBAC, kecamatan, halaman statis, bidang keahlian, pengaturan situs — konten demo otomatis DILEWATI karena `APP_ENV=production`).

Setelah selesai, buat akun Super Admin pertama:

```bash
make prod-admin        # interaktif: tanya email, nama, kata sandi (tersembunyi)
```

Perintah ini idempoten — bila email sudah terdaftar, akun tidak diduplikasi, hanya ditambahi perannya. Peran lain bisa dipilih dengan opsi `--role` (mis. `admin-web`), dan seluruh nilai bisa disebutkan langsung untuk pemakaian non-interaktif:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan icmi:admin --name="Nama" --email=admin@domain --password='...' --role=admin-web
```

Masuk lewat `/login` dengan akun tersebut, lalu buka **Konfigurasi Web** untuk mengatur identitas situs (nama, logo, hero, favicon, kontak, media sosial). Untuk dev, perintah yang sama tersedia sebagai `make admin`.

### 11.3.2 Deploy / rilis

```bash
./deploy.sh
# atau: make prod-deploy
```

Langkah yang dijalankan: build image → up -d → `migrate --force` → cache config/route/view/event. Dipakai untuk setiap rilis setelah instalasi awal.

Bila menambah permission baru di `RolePermissionSeeder`, jalankan ulang seedernya (idempoten):

```bash
make prod-seed-rbac
```

### 11.3.3 Perintah operasional

```bash
make prod-ps
make prod-logs s=app
make prod-migrate
make prod-artisan cmd="tinker"
make prod-shell
make prod-db-shell        # MariaDB client (kredensial dari env container db)
make prod-db-backup        # dump DB -> backups/icmi-<timestamp>.sql.gz (folder di-gitignore)
```

**phpMyAdmin produksi** — sengaja hanya bind ke `127.0.0.1` server (TIDAK terjangkau dari internet). Akses dari laptop lewat SSH tunnel:

```bash
ssh -L 8080:127.0.0.1:8080 user@server-produksi
# lalu buka http://localhost:8080 di browser — login dengan DB_USERNAME/DB_PASSWORD produksi
```

```bash
make prod-cache            # bangun ulang cache config/route/view/event
make prod-cache-clear      # optimize:clear (hapus semua cache)
make prod-seed-rbac        # ulangi seeder RBAC setelah menambah permission
make prod-seed-demo        # isi data demo lengkap (set DEMO_SEED=true, rebuild, seed) —
                           # akun demo superadmin@demo.test/password, WAJIB dibereskan saat go-live
make prod-restart
make prod-down          # matikan seluruh stack produksi
```

### 11.3.4 Rilis berikutnya (update kode)

```bash
git pull
./deploy.sh
```

`deploy.sh` idempotent — aman dijalankan berulang. Rollback = `git checkout <commit-sebelumnya> && ./deploy.sh` (pastikan migration backward-compatible, lihat [docs/09 §9.5](09-deployment-docker.md#95-alur-rilis-cicd)).

### 11.3.5 Backup penuh & pindah server

**Dari web (khusus database)**: menu admin **Backup & Restore** (`/admin/backup`, pemegang `settings.manage`) — unduh dump database ter-gzip dan pulihkan lewat unggah file (konfirmasi ketik `PULIHKAN`; MENIMPA seluruh data). File upload (foto/dokumen) TIDAK ikut — untuk pemindahan server penuh gunakan cara terminal di bawah.

```bash
make prod-backup    # dump DB + arsip file upload + salinan .env.production
                    # -> backups/icmi-full-<timestamp>/
```

Pindah server: salin folder backup + repo ke server baru, lalu:

```bash
# di server baru, setelah `make prod-install` selesai:
scp -r backups/icmi-full-<ts> user@server-baru:/var/www/icmibengkalis/backups/
make prod-restore dir=backups/icmi-full-<ts>   # minta konfirmasi ketik 'restore'
```

`prod-restore` MENIMPA seluruh database dan file upload dengan isi backup, lalu
membangun ulang cache aplikasi. `env.production.copy` di dalam folder backup berisi
konfigurasi lama (termasuk `APP_KEY` — penting: tanpa APP_KEY yang sama, data
terenkripsi lama tidak terbaca) — salin nilainya ke `.env.production` server baru
sebelum restore.

### 11.3.6 Checklist sebelum go-live

Lihat [docs/09 §9.7](09-deployment-docker.md#97-checklist-go-live) — checklist lengkap (HTTPS, firewall port DB/Redis, backup, dsb).

### 11.3.7 Troubleshooting produksi

- **Build gagal `composer install` HTTP 401 di `flux-pro`** (`The 'https://composer.fluxui.dev/...' URL required authentication`): `auth.json` belum ada / kredensialnya salah. Jalankan ulang lewat `make prod-install`/`prod-build`/`prod-deploy` atau `./deploy.sh` dari terminal interaktif — Anda akan diminta email + license key Flux dan `auth.json` dibuat otomatis (lihat §11.3.1). Bila kredensial salah ketik, hapus `auth.json` lalu jalankan ulang agar ditanya lagi. Pastikan juga Docker versi modern (BuildKit aktif) karena kredensial disuplai via `build.secrets`.
- **`dependency failed to start: container ...-db-1 is unhealthy` saat install pertama**: inisialisasi perdana MariaDB (membuat datadir + user) di server lambat bisa lebih lama dari jendela healthcheck. Healthcheck db kini punya `start_period: 120s` sehingga seharusnya tidak terjadi lagi; bila masih muncul, cek `docker ps` — kalau db sudah `(healthy)`, cukup jalankan ulang `make prod-install` (aman & idempoten).
- **`service "assets-init" didn't complete successfully: exit 1`**: cek lognya — `docker logs icmibengkalis-assets-init-1`. Bila `cp: ... Permission denied`, itu bug ownership volume yang sudah diperbaiki (servis kini `user: root`) — `git pull` lalu jalankan ulang. Penyebab lain akan terlihat jelas di log yang sama.
- **`nginx` gagal start / 502**: cek `make prod-logs s=app` — servis `assets-init` harus selesai (`service_completed_successfully`) sebelum nginx boleh start; cek `docker compose -f docker-compose.prod.yml ps assets-init`.
- **Variabel `${DB_DATABASE}` dsb. kosong saat `docker compose config`**: gunakan flag `--env-file .env.production` (sudah otomatis lewat `make prod-*`/`deploy.sh` — jangan panggil `docker compose -f docker-compose.prod.yml ...` manual tanpa flag ini).
- **Upload/dokumen hilang setelah redeploy**: pastikan tidak menjalankan `docker compose down -v` di produksi — flag `-v` menghapus volume `storage-data`/`dbdata` (data permanen). Cukup `make prod-down` (tanpa `-v`) untuk mematikan tanpa menghapus data.
