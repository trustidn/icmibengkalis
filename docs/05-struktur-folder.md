# 5. Struktur Folder Laravel yang Direkomendasikan

Prinsip: **struktur Laravel standar + pengelompokan per domain** di dalam folder bawaan. Tidak memakai paket module eksternal (nwidart dsb.) agar tooling Laravel/Livewire tetap mulus — modularitas dicapai lewat namespace yang disiplin.

```
icmibengkalis/
├── app/
│   ├── Enums/                          # Semua enum status & tipe
│   │   ├── MemberStatus.php
│   │   ├── PostStatus.php              # + method canTransitionTo()
│   │   ├── PostType.php
│   │   ├── ExpertiseLevel.php
│   │   ├── VerificationStatus.php
│   │   ├── DocumentAccessLevel.php
│   │   └── EventStatus.php
│   │
│   ├── Models/                         # Eloquent models, flat (konvensi Laravel)
│   │   ├── Concerns/                   # Trait bersama: HasSlug, Publishable
│   │   ├── Member.php
│   │   ├── MemberExpertise.php
│   │   ├── ExpertiseField.php
│   │   ├── OrgPeriod.php / OrgUnit.php / OrgAssignment.php
│   │   ├── Post.php / PostCategory.php / Tag.php
│   │   ├── Document.php / DocumentVersion.php / DocumentCategory.php
│   │   ├── Event.php / EventRegistration.php / EventReport.php
│   │   └── ...
│   │
│   ├── Services/                       # ★ Logika bisnis per domain
│   │   ├── Membership/
│   │   │   ├── MemberService.php
│   │   │   └── MemberImportService.php
│   │   ├── Expertise/
│   │   │   ├── ExpertiseService.php
│   │   │   └── ExpertSearchRepository.php   # repository hanya utk query kompleks
│   │   ├── Organization/OrgChartService.php # + salin struktur antar periode
│   │   ├── Publishing/PublishingService.php
│   │   ├── Archive/
│   │   │   ├── ArchiveService.php
│   │   │   └── TextExtractionService.php
│   │   ├── Events/
│   │   │   ├── EventService.php
│   │   │   └── CertificateService.php
│   │   └── Dashboard/StatisticsService.php  # agregasi + cache
│   │
│   ├── Livewire/                       # ★ Presentasi, dikelompokkan area + domain
│   │   ├── Public/                     # Halaman publik (full-page components)
│   │   │   ├── Home.php
│   │   │   ├── Posts/{Index,Show}.php
│   │   │   ├── Events/{Index,Show,Register}.php
│   │   │   ├── OrgChart.php
│   │   │   ├── Experts/{Index,Show,RequestSpeaker}.php
│   │   │   ├── Gallery/{Albums,Show}.php
│   │   │   └── Contact.php
│   │   ├── Member/                     # Area anggota (login)
│   │   │   ├── Profile/Edit.php
│   │   │   ├── Expertise/MyClaims.php
│   │   │   ├── Submissions/MyPosts.php # opini/artikel saya
│   │   │   └── Events/MyRegistrations.php
│   │   └── Admin/                      # Panel admin
│   │       ├── Dashboard.php
│   │       ├── Members/{Index,Form,Import}.php
│   │       ├── Expertise/{Fields,VerificationQueue}.php
│   │       ├── Organization/{Periods,UnitTree,AssignmentForm}.php
│   │       ├── Publishing/{Index,Form,ReviewQueue}.php
│   │       ├── Archive/{Index,Form,Versions}.php
│   │       ├── Events/{Index,Form,CheckInPanel,Reports,Certificates}.php
│   │       ├── MediaCenter/Assets.php
│   │       ├── Gallery/Albums.php
│   │       ├── Pages/Editor.php        # halaman statis
│   │       └── Settings/{General,UsersRoles}.php
│   │
│   ├── Policies/                       # 1 policy per model yang diotorisasi
│   ├── Http/
│   │   ├── Controllers/                # Hanya non-UI: unduhan file, QR, webhook,
│   │   │   ├── DocumentDownloadController.php
│   │   │   ├── CertificateVerifyController.php
│   │   │   └── SitemapController.php
│   │   ├── Middleware/
│   │   └── Requests/                   # Form Request utk controller non-Livewire
│   │
│   ├── Events/                         # Domain events: PostPublished, MemberRegistered,
│   ├── Listeners/                      #   ParticipantCheckedIn, DocumentUploaded...
│   ├── Jobs/                           # GenerateCertificates, ExtractDocumentText,
│   │                                   # SendNewsletter, RefreshDashboardCache
│   ├── Notifications/                  # Channel mail + database (siap WhatsApp channel)
│   ├── Data/                           # DTO sederhana (readonly class) per domain
│   ├── Support/                        # Helper: NiaGenerator, QrToken, Slug
│   └── Providers/
│
├── database/
│   ├── migrations/                     # Prefix urut per domain agar rapi
│   ├── seeders/
│   │   ├── RolePermissionSeeder.php    # ★ single source of truth RBAC
│   │   ├── DistrictSeeder.php          # 11 kecamatan Kab. Bengkalis
│   │   ├── ExpertiseFieldSeeder.php    # taksonomi awal kepakaran
│   │   └── DemoSeeder.php              # data contoh utk dev
│   └── factories/
│
├── resources/
│   ├── views/
│   │   ├── components/                 # Blade components lintas area
│   │   │   ├── layouts/{public,member,admin}.blade.php
│   │   │   └── org-chart/              # partial chart interaktif
│   │   └── livewire/                   # View per component (mengikuti struktur Livewire/)
│   ├── css/app.css                     # Tailwind + token Flux
│   └── js/
│       ├── app.js
│       └── org-chart.js                # logika zoom/pan/expand (Alpine plugin)
│
├── routes/
│   ├── web.php                         # publik
│   ├── member.php                      # prefix /akun, middleware auth+verified
│   ├── admin.php                       # prefix /admin, middleware auth+role/permission
│   └── console.php                     # scheduler: publish terjadwal, refresh statistik, backup
│
├── tests/
│   ├── Feature/                        # per domain: Membership/, Publishing/, ...
│   └── Unit/                           # enum transisi, NiaGenerator, dll.
│
├── docker/                             # lihat dok. 09
│   ├── nginx/default.conf
│   ├── php/Dockerfile
│   └── mysql/init.sql
├── docs/                               # dokumentasi ini
├── docker-compose.yml
├── docker-compose.prod.yml
└── .env.example
```

## Aturan yang menjaga struktur tetap rapi

1. **Satu domain = satu subfolder** di `Services/` dan `Livewire/{Area}/` — menambah modul baru berarti menambah subfolder, bukan menyentuh yang lain.
2. **Livewire component maksimal ±150 baris**; kalau membengkak, logika pindah ke Service.
3. **Route dipecah tiga file** (`web`, `member`, `admin`) dan didaftarkan di `bootstrap/app.php` — memudahkan audit middleware & permission per area.
4. **Enum untuk semua status** — tidak ada string status "magic" tersebar di kode.
5. **Migration diberi nama deskriptif** (`create_members_table`, `add_extracted_text_to_documents`) dan tidak diedit setelah dirilis; perubahan = migration baru.
6. **Seeder RBAC adalah kontrak**: penambahan permission selalu lewat `RolePermissionSeeder` + `php artisan permission:cache-reset`, tercatat di git.
