# 10. Panduan Teknis Pengembang

Dokumen onboarding untuk developer yang melanjutkan proyek ini. Baca dok. 02 (arsitektur) dan dok. 05 (struktur folder) terlebih dahulu.

---

## 10.1 Menjalankan Proyek Lokal

```bash
git clone <repo> && cd icmibengkalis
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed   # termasuk DemoSeeder di lokal
# app: http://localhost:8000 · mail: http://localhost:8025 · vite: otomatis
```

Akun demo hasil seeder: `superadmin@demo.test` / `password` (dan satu akun per peran). Jangan pernah menjalankan `DemoSeeder` di produksi.

## 10.2 Konvensi Kode

| Hal | Aturan |
|-----|--------|
| Gaya kode | Laravel Pint preset `laravel` — wajib lolos di CI (`vendor/bin/pint --test`) |
| Analisis statis | Larastan level 6 minimum |
| Bahasa | Kode & identifier Inggris; teks UI Bahasa Indonesia via `lang/id/*.php` (jangan hardcode di Blade) |
| Penamaan Livewire | `App\Livewire\{Area}\{Domain}\{Aksi}` — Area: `Public`, `Member`, `Admin` |
| Status | Selalu PHP Enum dengan method transisi; dilarang string literal status |
| Commit | Conventional Commits (`feat:`, `fix:`, `docs:`...) per satu perubahan logis |
| Branch | `main` (produksi) ← PR dari `feat/*`, `fix/*`; CI hijau sebelum merge |

## 10.3 Resep: Menambah Modul/Fitur Baru

Urutan baku (contoh fitur "Program Kerja per Divisi"):

1. **Migration + Model + Factory** — `php artisan make:model WorkProgram -mf`; definisikan relasi & casts.
2. **Enum** bila ada status.
3. **Policy** — `make:policy WorkProgramPolicy --model=WorkProgram`; daftarkan permission baru di `RolePermissionSeeder` lalu `php artisan db:seed --class=RolePermissionSeeder` (seeder bersifat idempoten/upsert).
4. **Service** di `app/Services/{Domain}/` berisi use-case; tulis unit test-nya.
5. **Livewire component** di area yang tepat + view; component hanya memanggil Service.
6. **Route** di file area (`admin.php`) dengan middleware `can:` yang sesuai.
7. **Menu** — tambah item sidebar dengan pengecekan permission yang sama.
8. **Feature test** minimal: happy path + akses ditolak untuk peran tanpa izin.
9. Perbarui dok. 03 (ERD) dan 07 (dependensi modul) bila menambah entitas.

## 10.4 Pola Penting yang Dipakai di Kode

### Otorisasi dua tingkat
```php
// routes/admin.php — gerbang modul
Route::middleware('can:publishing.manage')->group(...);

// Dalam component/service — gerbang record
$this->authorize('update', $post);   // PostPolicy: admin divisi hanya post divisinya
```

### Transisi status lewat Enum
```php
enum PostStatus: string {
    case Draft = 'draft'; case InReview = 'in_review';
    case Published = 'published'; case Rejected = 'rejected';

    public function canTransitionTo(self $to): bool { /* peta transisi */ }
}
// PublishingService melempar DomainException bila transisi ilegal — satu-satunya pintu ubah status.
```

### File privat
Semua file arsip di disk `private`; unduhan hanya lewat `DocumentDownloadController` yang memanggil Policy lalu `streamDownload`. **Jangan pernah** menyimpan file terproteksi di `storage/app/public`.

### Pekerjaan berat = Job
Generate sertifikat massal, ekstraksi teks PDF, kirim newsletter, refresh statistik → Job di queue `default`; email di queue `mail`. Pantau lewat Horizon (`/horizon`, khusus Super Admin).

### Cache
- Konten publik di-cache dengan key berpola `public.{tipe}.{slug}` TTL 10 menit.
- Listener event konten (`PostPublished`, dll.) membersihkan key terkait — bukan `cache:clear` global.
- Statistik dashboard: `StatisticsService` menulis ke Redis tiap 15 menit via scheduler.

## 10.5 Testing

```bash
docker compose exec app php artisan test            # seluruh suite (Pest)
docker compose exec app php artisan test --filter=Publishing
```

Kebijakan: setiap use-case Service punya feature test; alur kritis wajib ter-cover — workflow publikasi, hak akses arsip, check-in QR (token invalid/dobel), verifikasi kepakaran, matriks peran (smoke test semua route admin per peran).

## 10.6 Operasional Rutin

| Aktivitas | Frekuensi | Cara |
|-----------|-----------|------|
| Backup | Harian (otomatis) | spatie/laravel-backup, cek notifikasi gagal |
| Uji restore backup | Kuartalan | prosedur dok. 09 §9.6 |
| Update dependensi patch/minor | Bulanan | `composer update` + test hijau |
| Update Laravel mayor | Per rilis | branch khusus, ikuti upgrade guide |
| Review permission & akun | Per pergantian pengurus | nonaktifkan akun, jalankan ulang seeder peran |
| Rotasi log & pemantauan disk | Otomatis + cek bulanan | logrotate container, alert disk 80% |

## 10.7 Troubleshooting Cepat

| Gejala | Periksa |
|--------|---------|
| Perubahan permission tidak berefek | `php artisan permission:cache-reset` |
| Email tidak terkirim | Container `queue` hidup? `failed_jobs`? Kredensial SMTP? |
| File 404 setelah deploy | `php artisan storage:link` dijalankan? Volume `storage-public` ter-mount di nginx? |
| Livewire error setelah update | `php artisan view:clear` + hard refresh (asset Vite lama) |
| Konten publik tidak ter-update | Cache konten — cek listener pembersih cache pada event terkait |
| Statistik dashboard kosong | Scheduler jalan? `php artisan schedule:list`, cek job `RefreshDashboardCache` |

## 10.8 Prinsip untuk Tim Berikutnya

1. **Logika bisnis hanya di Service** — kalau ragu di mana menaruh kode, jawabannya hampir selalu Service.
2. **Jangan menambah pola baru** (repository penuh, CQRS, dsb.) tanpa ADR tertulis di dok. 02 §2.5.
3. **Dokumen ini hidup** — setiap PR yang mengubah skema, permission, atau alur utama wajib memperbarui dokumen terkait di `docs/`.
4. **Fitur AI/integrasi masa depan** sudah punya titik sambung yang disiapkan (dok. 02 §2.6) — bangun di titik itu, jangan menempel di tempat lain.
