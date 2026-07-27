# 3. Desain Basis Data (ERD)

Konvensi: tabel `snake_case` jamak, PK `id` (bigint), FK `<tabel_tunggal>_id`, semua tabel memiliki `created_at`/`updated_at`, soft delete (`deleted_at`) pada entitas penting (members, documents, posts, events).

---

## 3.1 ERD — Keanggotaan & Kepakaran

```mermaid
erDiagram
    users ||--o| members : "1 akun ↔ 1 profil (opsional)"
    districts ||--o{ members : berdomisili
    professions ||--o{ members : berprofesi
    members ||--o{ member_educations : memiliki
    members ||--o{ member_organizations : riwayat
    members ||--o{ member_publications : menulis
    members ||--o{ member_certifications : memiliki
    members ||--o{ member_status_histories : riwayat_status
    members ||--o{ member_expertises : mengklaim
    expertise_fields ||--o{ member_expertises : bidang
    expertise_fields ||--o{ expertise_fields : "parent (hirarki)"
    members ||--o{ expert_requests : "ditugaskan (opsional)"

    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        string two_factor_secret "nullable"
        boolean is_active
    }
    members {
        bigint id PK
        bigint user_id FK "nullable, unique"
        string nia UK
        string full_name
        string title_prefix "gelar depan"
        string title_suffix "gelar belakang"
        string gender
        string birth_place
        date birth_date
        text address
        bigint district_id FK
        string institution "instansi"
        bigint profession_id FK
        json social_links
        enum status "aktif|tidak_aktif|alumni|meninggal"
        date joined_at
        boolean show_contact_public
    }
    districts {
        bigint id PK
        string name "kecamatan"
    }
    professions {
        bigint id PK
        string name
    }
    member_educations {
        bigint id PK
        bigint member_id FK
        enum level "S1|S2|S3|D3|SMA|lainnya"
        string institution
        string major
        year graduated_year
    }
    member_expertises {
        bigint id PK
        bigint member_id FK
        bigint expertise_field_id FK
        enum level "pemula|menengah|pakar"
        text evidence "ringkasan bukti"
        enum status "diajukan|terverifikasi|ditolak"
        bigint verified_by FK "users, nullable"
        timestamp verified_at
        boolean available_as_speaker
        json speaker_topics
    }
    expertise_fields {
        bigint id PK
        bigint parent_id FK "nullable"
        string name
        string slug UK
        text description
    }
    expert_requests {
        bigint id PK
        string requester_name
        string requester_institution
        string requester_email
        string requester_phone
        text topic
        date needed_date
        enum status "baru|diproses|diteruskan|selesai|ditolak"
        bigint assigned_member_id FK "nullable"
        bigint handled_by FK "users, nullable"
    }
```

Foto profil, dokumen sertifikasi, dan bukti kepakaran disimpan melalui **Spatie Media Library** (tabel `media`, relasi polimorfik) — tidak digambar ulang di setiap diagram.

## 3.2 ERD — Struktur Organisasi

```mermaid
erDiagram
    org_periods ||--o{ org_units : memiliki
    org_units ||--o{ org_units : "parent (pohon divisi)"
    org_units ||--o{ org_assignments : berisi
    members ||--o{ org_assignments : menjabat

    org_periods {
        bigint id PK
        string name "cth: 2025-2030"
        date starts_at
        date ends_at
        boolean is_active
    }
    org_units {
        bigint id PK
        bigint org_period_id FK
        bigint parent_id FK "nullable"
        string name "cth: Bidang Ekonomi"
        int sort_order
    }
    org_assignments {
        bigint id PK
        bigint org_unit_id FK
        bigint member_id FK
        string position_title "cth: Ketua Bidang"
        int sort_order
        text short_bio "riwayat singkat utk profil chart"
        boolean show_contact
    }
```

Satu pohon `org_units` per periode → filter periode pada org chart cukup mengganti `org_period_id`. Duplikasi struktur ke periode baru disediakan sebagai aksi admin ("salin struktur periode").

## 3.3 ERD — Publikasi & Konten Publik

```mermaid
erDiagram
    users ||--o{ posts : menulis
    post_categories ||--o{ posts : mengkategorikan
    posts ||--o{ post_revisions : riwayat
    tags ||--o{ taggables : "polimorfik (posts, documents, events)"
    newsletters ||--o{ newsletter_items : berisi
    posts ||--o{ newsletter_items : dirujuk
    org_units ||--o{ posts : "divisi pemilik (nullable)"

    posts {
        bigint id PK
        enum type "berita|artikel|opini|press_release"
        string title
        string slug UK
        text excerpt
        longtext body
        enum status "draft|in_review|published|rejected|archived"
        bigint author_id FK
        bigint post_category_id FK "nullable"
        bigint org_unit_id FK "nullable"
        bigint reviewed_by FK "nullable"
        text review_note
        timestamp published_at
        int view_count
        json seo_meta
    }
    post_categories {
        bigint id PK
        string name
        string slug UK
    }
    tags {
        bigint id PK
        string name UK
        string slug UK
    }
    taggables {
        bigint tag_id FK
        bigint taggable_id
        string taggable_type
    }
    post_revisions {
        bigint id PK
        bigint post_id FK
        bigint edited_by FK
        json snapshot
    }
    newsletters {
        bigint id PK
        string subject
        enum status "draft|terkirim"
        timestamp sent_at
    }
    newsletter_subscribers {
        bigint id PK
        string email UK
        timestamp verified_at
        timestamp unsubscribed_at
    }
    announcements {
        bigint id PK
        string title
        text body
        date starts_at
        date ends_at
        boolean is_pinned
    }
    pages {
        bigint id PK
        string slug UK "sejarah|visi-misi|sambutan-ketua|..."
        string title
        longtext body
        json seo_meta
        bigint updated_by FK
    }
    contact_messages {
        bigint id PK
        string name
        string email
        string subject
        text message
        enum status "baru|dibaca|dibalas"
    }
    page_views {
        bigint id PK
        string path
        string viewable_type "nullable"
        bigint viewable_id "nullable"
        date viewed_on
        int count "agregat harian"
    }
```

## 3.4 ERD — Arsip Digital

```mermaid
erDiagram
    document_categories ||--o{ documents : mengkategorikan
    document_categories ||--o{ document_categories : parent
    documents ||--o{ document_versions : versi
    users ||--o{ documents : mengunggah
    org_units ||--o{ documents : "pemilik (nullable)"
    documents ||--o{ document_permissions : "akses tambahan"

    documents {
        bigint id PK
        string title
        string document_number "nomor surat/SK, nullable"
        enum doc_type "surat|notulen|rapat|sk|sop|foto|video|seminar|buku|artikel|lainnya"
        text description
        bigint document_category_id FK
        bigint org_unit_id FK "nullable"
        bigint uploaded_by FK
        enum access_level "publik|anggota|pengurus|terbatas"
        date document_date
        longtext extracted_text "utk full-text search"
        int current_version
    }
    document_versions {
        bigint id PK
        bigint document_id FK
        int version_number
        bigint uploaded_by FK
        text change_note
        string file_hash
    }
    document_permissions {
        bigint id PK
        bigint document_id FK
        string grantee_type "role|org_unit|user"
        bigint grantee_id
        enum ability "view|manage"
    }
    document_categories {
        bigint id PK
        bigint parent_id FK "nullable"
        string name
        string slug UK
    }
```

File fisik tiap versi disimpan di Media Library dengan collection `versions` pada model `DocumentVersion`; riwayat perubahan lain dicatat `activity_log`.

## 3.5 ERD — Kegiatan, Galeri, Media Center

```mermaid
erDiagram
    org_units ||--o{ events : menyelenggarakan
    events ||--o{ event_registrations : registrasi
    members ||--o{ event_registrations : "peserta anggota (nullable)"
    events ||--o| event_reports : laporan
    event_registrations ||--o| event_certificates : sertifikat
    events ||--o| albums : dokumentasi

    events {
        bigint id PK
        string title
        string slug UK
        text description
        datetime starts_at
        datetime ends_at
        enum venue_type "luring|daring|hybrid"
        string location
        string meeting_url
        bigint org_unit_id FK "nullable"
        int quota "nullable"
        boolean open_for_public
        enum status "draft|published|selesai|dibatalkan"
        boolean registration_open
    }
    event_registrations {
        bigint id PK
        bigint event_id FK
        bigint member_id FK "nullable"
        string name
        string email
        string phone
        string qr_token UK
        enum status "terdaftar|hadir|batal"
        timestamp checked_in_at
        bigint checked_in_by FK "nullable"
    }
    event_reports {
        bigint id PK
        bigint event_id FK
        text summary
        int participant_count
        int attendance_count
        decimal budget_planned
        decimal budget_realized
        bigint submitted_by FK
    }
    event_certificates {
        bigint id PK
        bigint event_registration_id FK
        string certificate_number UK
        string verify_code UK
        timestamp generated_at
    }
    albums {
        bigint id PK
        string title
        string slug UK
        enum type "foto|video"
        text description
        bigint event_id FK "nullable"
        boolean is_published
    }
    album_items {
        bigint id PK
        bigint album_id FK
        string youtube_url "nullable (video)"
        string caption
        int sort_order
    }
    media_asset_categories {
        bigint id PK
        string name "logo|template surat|..."
    }
    media_assets {
        bigint id PK
        bigint media_asset_category_id FK
        string title
        text description
        enum access_level "publik|anggota|pengurus"
        int download_count
    }
```

## 3.6 Tabel Paket Pihak Ketiga

| Tabel | Sumber | Fungsi |
|-------|--------|--------|
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | spatie/laravel-permission | RBAC |
| `media` | spatie/laravel-medialibrary | Semua file (foto, dokumen, poster, aset) |
| `activity_log` | spatie/laravel-activitylog | Audit trail |
| `jobs`, `failed_jobs`, `job_batches` | Laravel | Queue |
| `cache`, `sessions` | Laravel | Bila tidak memakai Redis |
| `notifications` | Laravel | Notifikasi database |

## 3.7 Indeks & Catatan Kinerja

- Indeks unik: `members.nia`, `posts.slug`, `events.slug`, `event_registrations.qr_token`, `event_certificates.verify_code`.
- Indeks komposit: `posts (type, status, published_at)`, `member_expertises (expertise_field_id, status)`, `page_views (path, viewed_on)` unik untuk agregasi harian.
- `FULLTEXT` index pada `documents (title, description, extracted_text)` dan `posts (title, body)` — dipakai Scout database driver; siap dipindah ke Meilisearch tanpa ubah skema.
- Statistik dashboard dibaca dari cache Redis, bukan agregasi langsung saat request.
