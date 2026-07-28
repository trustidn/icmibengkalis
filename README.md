# Portal Digital ICMI Kabupaten Bengkalis

Portal resmi Ikatan Cendekiawan Muslim se-Indonesia (ICMI) Organisasi Daerah Kabupaten Bengkalis — pusat informasi, manajemen organisasi, publikasi, dan pengelolaan pengetahuan.

## Dokumentasi Proyek

| No | Dokumen | Isi |
|----|---------|-----|
| 1 | [SRS — Spesifikasi Kebutuhan](docs/01-srs.md) | Kebutuhan fungsional & non-fungsional seluruh modul |
| 2 | [Arsitektur Aplikasi](docs/02-arsitektur.md) | Lapisan arsitektur, pola desain, alur request |
| 3 | [Desain Basis Data (ERD)](docs/03-database-erd.md) | ERD lengkap + kamus data |
| 4 | [Diagram UML](docs/04-uml.md) | Use Case, Activity, Sequence, Class Diagram |
| 5 | [Struktur Folder Laravel](docs/05-struktur-folder.md) | Layout modular yang direkomendasikan |
| 6 | [Roadmap Implementasi](docs/06-roadmap.md) | Tahapan pengembangan per fase |
| 7 | [Modul & Dependensi](docs/07-modul-dependensi.md) | Daftar modul, paket, dan keterkaitannya |
| 8 | [Wireframe & Alur Navigasi](docs/08-wireframe-navigasi.md) | Sketsa antarmuka & peta navigasi |
| 9 | [Deployment Docker Compose](docs/09-deployment-docker.md) | Strategi & topologi deployment dev & produksi |
| 10 | [Panduan Teknis Pengembang](docs/10-panduan-teknis.md) | Konvensi kode, cara menambah modul, testing |
| 11 | [Panduan Menjalankan Docker](docs/11-panduan-docker.md) | Langkah praktis dari nol: setup, perintah harian, troubleshooting |
| 12 | [Naskah Presentasi Portal](docs/12-naskah-presentasi.md) | Naskah demo fitur lengkap untuk pengurus/anggota/tamu + contekan tanya-jawab |

## Ringkasan Teknologi

- **Backend**: Laravel 12 (PHP 8.3+), Livewire 3
- **UI**: Flux UI, Tailwind CSS 4, Alpine.js
- **Database**: MariaDB/MySQL, Redis (cache, queue, session)
- **Paket kunci**: Spatie Permission, Spatie Media Library, Laravel Scout (full-text search)
- **Infrastruktur**: Docker Compose, storage lokal / S3-compatible

## Modul Sistem

1. Website Publik
2. Struktur Organisasi Interaktif
3. Database Anggota
4. **Direktori Kepakaran Anggota (Expert Directory)** — pembeda utama portal ini
5. Arsip Digital
6. Manajemen Kegiatan
7. Publikasi (workflow Draft → Review → Publish)
8. Dashboard Organisasi
9. Media Center
10. Sistem Hak Akses (RBAC Spatie)

## Menjalankan dengan Docker

### Development

```bash
make install   # .env otomatis + up + composer install + key:generate + migrate --seed
```

- App: http://localhost:8000
- Vite (HMR): http://localhost:5173
- Mailpit (uji email): http://localhost:8025
- MariaDB: localhost:3306 (`icmi` / `secret`)
- phpMyAdmin: http://localhost:8080 (di produksi hanya via SSH tunnel — lihat docs/11 §11.3.3)

Perintah lain: `make up`, `make down`, `make logs s=app`, `make shell`, `make artisan cmd="route:list"`, `make composer cmd="require pkg/name"`, `make npm cmd="run build"`, `make test`, `make pint`, `make fresh`. Lihat `make help` untuk daftar lengkap.

### Production

```bash
make prod-env          # salin template .env.production
nano .env.production   # isi DB_PASSWORD, APP_URL, MAIL_*, dsb. (APP_KEY boleh kosong)
make prod-install      # APP_KEY otomatis + build + up + migrate + cache + seeder RBAC
```

Rilis berikutnya: `git pull` lalu `./deploy.sh` (atau `make prod-deploy`). Operasional: `make prod-logs s=app`, `make prod-db-backup`, `make prod-cache-clear`, `make prod-seed-rbac`, dan lainnya — lihat `make help`.

Panduan lengkap step-by-step (termasuk troubleshooting) di [docs/11-panduan-docker.md](docs/11-panduan-docker.md); rancangan arsitektur & topologi di [docs/09-deployment-docker.md](docs/09-deployment-docker.md).
