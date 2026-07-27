# 1. Spesifikasi Kebutuhan Perangkat Lunak (SRS)

**Proyek**: Portal Digital ICMI Kabupaten Bengkalis
**Versi dokumen**: 1.0 — Juli 2026

---

## 1.1 Pendahuluan

### Tujuan Dokumen
Dokumen ini mendefinisikan kebutuhan fungsional dan non-fungsional Portal Digital ICMI Kabupaten Bengkalis sebagai acuan bagi tim pengembang, penguji, dan pemangku kepentingan organisasi.

### Ruang Lingkup Sistem
Portal berfungsi sebagai:
1. Identitas digital resmi ICMI Kabupaten Bengkalis.
2. Pusat informasi kegiatan organisasi.
3. Database anggota dan kepakaran anggota.
4. Pusat arsip digital organisasi.
5. Media publikasi karya dan pemikiran anggota.
6. Dashboard informasi dan statistik organisasi.
7. Fondasi transformasi digital jangka panjang (SSO, mobile app, API, AI).

### Definisi & Istilah

| Istilah | Definisi |
|---------|----------|
| NIA | Nomor Induk Anggota, identitas unik anggota ICMI |
| Orda | Organisasi Daerah (tingkat kabupaten/kota) |
| Pengurus | Anggota yang menjabat pada struktur organisasi untuk satu periode |
| Kepakaran | Bidang keahlian anggota yang terverifikasi |
| Arsip | Dokumen digital resmi organisasi yang dikelola dengan versi & hak akses |
| RBAC | Role-Based Access Control (kontrol akses berbasis peran) |

### Pengguna Sistem (Aktor)

| Aktor | Deskripsi |
|-------|-----------|
| Publik | Pengunjung tanpa login; mengakses konten publik |
| Anggota | Anggota ICMI terdaftar; mengelola profil sendiri, mengirim opini/artikel, mendaftar kegiatan |
| Admin Divisi | Mengelola konten & kegiatan milik divisinya |
| Ketua Divisi | Menyetujui konten/kegiatan divisi, melihat laporan divisi |
| Bendahara | Mengelola data terkait keuangan kegiatan (laporan) |
| Sekretaris | Mengelola arsip, surat, agenda, dan administrasi keanggotaan |
| Ketua | Melihat seluruh dashboard, menyetujui publikasi tingkat organisasi |
| Super Admin | Kontrol penuh: pengguna, peran, konfigurasi, seluruh modul |

---

## 1.2 Kebutuhan Fungsional

Kode kebutuhan: `F-<modul>-<nomor>`. Prioritas: **M** (Must), **S** (Should), **C** (Could) — mengikuti MoSCoW.

### F-PUB — Website Publik

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-PUB-01 | Menampilkan beranda: hero, berita terbaru, agenda terdekat, statistik ringkas, galeri | M |
| F-PUB-02 | Halaman statis terkelola CMS: Tentang ICMI, Sejarah, Visi & Misi, Sambutan Ketua, Program Kerja | M |
| F-PUB-03 | Daftar & detail Berita, Artikel dengan kategori, tag, pencarian, dan pagination | M |
| F-PUB-04 | Daftar Agenda kegiatan (mendatang & arsip) dengan detail lokasi/waktu | M |
| F-PUB-05 | Pengumuman resmi dengan penanda periode tayang | M |
| F-PUB-06 | Galeri Foto (album) dan Galeri Video (embed YouTube/unggahan) | M |
| F-PUB-07 | Halaman Kontak dengan formulir pesan (dilindungi rate-limit & honeypot) | M |
| F-PUB-08 | SEO: meta tag, Open Graph, sitemap.xml, slug ramah URL | S |
| F-PUB-09 | Dark mode mengikuti preferensi sistem dengan toggle manual | S |
| F-PUB-10 | Penghitung kunjungan halaman untuk statistik dashboard | S |

### F-ORG — Struktur Organisasi Interaktif

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-ORG-01 | Organizational chart interaktif: zoom in/out, pan, expand/collapse per divisi | M |
| F-ORG-02 | Klik node pengurus membuka profil: foto, nama, jabatan, bidang keahlian, riwayat singkat, kontak (opsional), masa jabatan | M |
| F-ORG-03 | Pencarian nama pengurus dengan highlight node hasil | M |
| F-ORG-04 | Filter berdasarkan periode kepengurusan (multi-periode tersimpan) | M |
| F-ORG-05 | Seluruh struktur (periode, divisi, jabatan, penugasan) dikelola dari panel admin tanpa mengubah kode | M |
| F-ORG-06 | Responsif: mode pohon di desktop, mode daftar bertingkat di mobile | M |
| F-ORG-07 | Ekspor struktur sebagai gambar (PNG) untuk kebutuhan cetak | C |

### F-MEM — Database Anggota

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-MEM-01 | Profil anggota: NIA, nama, gelar (depan/belakang), foto, tempat/tanggal lahir, alamat, kecamatan, instansi, profesi, bidang keahlian, pendidikan, riwayat organisasi, publikasi, sertifikasi, media sosial, status keanggotaan | M |
| F-MEM-02 | NIA unik, dapat digenerate otomatis dengan format terkonfigurasi | M |
| F-MEM-03 | Pencarian & filter: kecamatan, profesi, bidang keahlian, jenjang pendidikan, status keanggotaan | M |
| F-MEM-04 | Anggota dapat memperbarui profil sendiri; perubahan field kunci (NIA, status) hanya oleh admin | M |
| F-MEM-05 | Status keanggotaan: aktif, tidak aktif, alumni/pindah, meninggal — dengan riwayat perubahan | M |
| F-MEM-06 | Impor anggota massal dari Excel/CSV dengan validasi & laporan galat | S |
| F-MEM-07 | Ekspor daftar anggota (Excel/PDF) sesuai hak akses | S |
| F-MEM-08 | Kartu anggota digital (QR berisi NIA terverifikasi) | C |
| F-MEM-09 | Pendaftaran anggota baru daring dengan alur verifikasi admin | S |

### F-EXP — Direktori Kepakaran (Expert Directory)

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-EXP-01 | Taksonomi bidang kepakaran hirarkis (rumpun → bidang → sub-bidang), dikelola admin | M |
| F-EXP-02 | Anggota dapat mengklaim kepakaran dengan tingkat (pemula/menengah/pakar) + bukti (publikasi, sertifikat, pengalaman) | M |
| F-EXP-03 | Verifikasi klaim kepakaran oleh admin/tim; hanya yang terverifikasi tampil di direktori publik | M |
| F-EXP-04 | Pencarian pakar berdasarkan: bidang keahlian, pendidikan, profesi, pengalaman, kata kunci topik | M |
| F-EXP-05 | Halaman profil pakar publik: ringkasan kepakaran, publikasi, riwayat, tautan kontak organisasi (bukan kontak pribadi langsung) | M |
| F-EXP-06 | Formulir permintaan narasumber: pihak eksternal mengajukan permintaan → sekretariat meneruskan ke pakar | S |
| F-EXP-07 | Rekap kepakaran untuk dashboard: sebaran bidang, jumlah pakar terverifikasi per bidang | S |
| F-EXP-08 | Penilaian kesiapan menjadi narasumber (bersedia/tidak, topik yang diampu) | S |

### F-ARC — Arsip Digital

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-ARC-01 | Unggah dokumen: surat, notulen, dokumen rapat, SK, SOP, foto, video, hasil seminar, buku, artikel, lainnya | M |
| F-ARC-02 | Kategori hirarkis + tag bebas per dokumen | M |
| F-ARC-03 | Versi dokumen: unggah versi baru menyimpan riwayat, dapat dilihat & diunduh per versi | M |
| F-ARC-04 | Hak akses per dokumen: publik / anggota / peran tertentu / divisi tertentu | M |
| F-ARC-05 | Riwayat perubahan (audit log): siapa, kapan, aksi apa | M |
| F-ARC-06 | Preview dokumen di browser (PDF, gambar; office via konversi PDF) | S |
| F-ARC-07 | Full-text search pada judul, deskripsi, dan isi dokumen (ekstraksi teks PDF/Office) | S |
| F-ARC-08 | Nomor arsip otomatis mengikuti tata naskah organisasi | C |
| F-ARC-09 | Masa retensi & status arsip (aktif/inaktif/statis) | C |

### F-EVT — Manajemen Kegiatan

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-EVT-01 | CRUD agenda kegiatan: judul, deskripsi, waktu, lokasi/daring, penyelenggara (divisi), kuota, poster | M |
| F-EVT-02 | Registrasi peserta daring (anggota & publik) dengan konfirmasi email | M |
| F-EVT-03 | QR code unik per peserta untuk check-in kehadiran | M |
| F-EVT-04 | Panel check-in: pindai QR / cari nama, catat waktu hadir | M |
| F-EVT-05 | Dokumentasi kegiatan: album foto/video terhubung galeri | S |
| F-EVT-06 | Laporan kegiatan: ringkasan, jumlah peserta vs hadir, anggaran-realisasi, lampiran | S |
| F-EVT-07 | Sertifikat digital: template per kegiatan, generate massal PDF, verifikasi via QR/kode unik | S |
| F-EVT-08 | Pengingat otomatis H-1 via email kepada peserta terdaftar (queue) | C |

### F-PBL — Publikasi

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-PBL-01 | Jenis konten: Berita, Artikel, Opini Anggota, Press Release, Newsletter | M |
| F-PBL-02 | Workflow status: Draft → In Review → Published (dengan Rejected/Revisi) | M |
| F-PBL-03 | Anggota mengirim Opini/Artikel → masuk antrean review editor | M |
| F-PBL-04 | Editor (Sekretaris/Admin) mereview: setujui, tolak dengan catatan, atau sunting | M |
| F-PBL-05 | Penjadwalan publikasi (publish at) | S |
| F-PBL-06 | Editor konten rich-text dengan unggah gambar inline | M |
| F-PBL-07 | Newsletter: kompilasi konten terpilih, kirim ke pelanggan email (queue) | C |
| F-PBL-08 | Riwayat revisi konten | C |

### F-DSH — Dashboard Organisasi

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-DSH-01 | Kartu statistik: total anggota, anggota baru (periode berjalan), total kegiatan, total publikasi, total arsip | M |
| F-DSH-02 | Grafik sebaran: profesi, bidang keahlian, kecamatan, jenjang pendidikan | M |
| F-DSH-03 | Grafik tren: pertumbuhan anggota per bulan, kegiatan per bulan, publikasi per bulan | M |
| F-DSH-04 | Statistik website: kunjungan halaman, konten terpopuler | S |
| F-DSH-05 | Statistik arsip: jumlah per kategori, unggahan terbaru | S |
| F-DSH-06 | Dashboard menyesuaikan peran (divisi hanya melihat datanya) | S |
| F-DSH-07 | Data dashboard di-cache dan diperbarui berkala (near real-time) | S |

### F-MED — Media Center

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-MED-01 | Repositori aset resmi: logo, brand guideline, template presentasi/surat/poster/publikasi | M |
| F-MED-02 | Kategori aset + pratinjau + unduh | M |
| F-MED-03 | Hak akses: aset publik vs internal anggota/pengurus | S |
| F-MED-04 | Penghitung unduhan per aset | C |

### F-ACL — Sistem Hak Akses & Autentikasi

| Kode | Kebutuhan | Prioritas |
|------|-----------|-----------|
| F-ACL-01 | Autentikasi email + password, verifikasi email, reset password | M |
| F-ACL-02 | Peran: Super Admin, Ketua, Sekretaris, Bendahara, Ketua Divisi, Admin Divisi, Anggota (Publik = guest) | M |
| F-ACL-03 | Permission granular per modul (lihat matriks §1.4) via Spatie Permission | M |
| F-ACL-04 | Policy Laravel untuk otorisasi level-record (mis. admin divisi hanya konten divisinya) | M |
| F-ACL-05 | Two-Factor Authentication (TOTP) untuk peran admin | S |
| F-ACL-06 | Audit log aktivitas penting (login, perubahan data sensitif) | S |

---

## 1.3 Kebutuhan Non-Fungsional

| Kode | Kategori | Kebutuhan |
|------|----------|-----------|
| NF-01 | Kinerja | Halaman publik TTFB < 500 ms (dengan cache); dukung 200 pengguna bersamaan |
| NF-02 | Kinerja | Query daftar ter-pagination; gambar dioptimasi (WebP, responsive, lazy-load) via Media Library conversions |
| NF-03 | Keamanan | Proteksi CSRF, XSS (escape Blade), SQL injection (Eloquent), rate limiting login & form publik |
| NF-04 | Keamanan | Password hash Argon2id/bcrypt; file privat dilayani via signed/streamed URL, bukan path publik |
| NF-05 | Keamanan | Validasi unggahan: tipe MIME, ukuran maksimum, penamaan ulang file |
| NF-06 | Ketersediaan | Backup otomatis harian database & storage; target RPO 24 jam |
| NF-07 | Usabilitas | Responsif (mobile-first), dark mode, aksesibilitas dasar WCAG AA (kontras, alt text, navigasi keyboard) |
| NF-08 | Usabilitas | Seluruh antarmuka berbahasa Indonesia; struktur i18n disiapkan (`lang/id`) |
| NF-09 | Maintainability | Arsitektur modular (lihat dok. 02); coverage test fitur inti; CI menjalankan lint + test |
| NF-10 | Skalabilitas | Stateless app container; session/cache/queue di Redis; storage dapat dipindah ke S3 tanpa ubah kode (flysystem) |
| NF-11 | Kepatuhan | Data pribadi anggota mengikuti UU PDP: consent, hak akses/hapus data, kontak pribadi tidak tampil publik tanpa izin |
| NF-12 | SEO | Skor Lighthouse ≥ 90 (Performance, SEO, Best Practices) untuk halaman publik |
| NF-13 | Auditabilitas | Aktivitas admin tercatat (spatie/laravel-activitylog) minimal 12 bulan |

---

## 1.4 Matriks Peran × Modul (ringkas)

Legenda: **F** = akses penuh, **K** = kelola milik sendiri/divisinya, **R** = review/approve, **B** = baca, **–** = tanpa akses.

| Modul | Super Admin | Ketua | Sekretaris | Bendahara | Ketua Divisi | Admin Divisi | Anggota | Publik |
|-------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Halaman & konten publik | F | R | F | B | R (divisi) | K (divisi) | B | B |
| Struktur organisasi | F | B | F | B | B | B | B | B |
| Database anggota | F | B | F | B | B (divisi) | B (divisi) | K (profil sendiri) | B (direktori terbatas) |
| Direktori kepakaran | F | B | R (verifikasi) | B | B | B | K (klaim sendiri) | B (terverifikasi) |
| Arsip digital | F | B | F | B (keuangan) | K (divisi) | K (divisi) | B (sesuai akses) | B (publik saja) |
| Kegiatan | F | R | F | B (laporan) | R (divisi) | K (divisi) | daftar/hadir | daftar (publik) |
| Publikasi | F | R | R (editor) | – | R (divisi) | K (draft) | K (opini sendiri) | B |
| Dashboard | F | F | F | F (keuangan) | F (divisi) | B (divisi) | – | – |
| Media center | F | B | F | B | B | K (divisi) | B | B (publik) |
| Pengguna & peran | F | – | – | – | – | – | – | – |

Matriks permission granular lengkap didefinisikan sebagai seeder (`RolePermissionSeeder`) dan menjadi single source of truth.

---

## 1.5 Asumsi & Batasan

- Satu instansi portal untuk satu Orda (bukan multi-tenant); desain data tidak menutup kemungkinan multi-Orda di masa depan (kolom `orda_id` dapat ditambahkan).
- Bahasa antarmuka: Indonesia. Konten kepakaran dapat memiliki istilah Inggris.
- Video besar direkomendasikan di-embed dari YouTube; unggahan video dibatasi ukuran.
- Integrasi WhatsApp/SSO/AI berada di luar lingkup rilis 1.0 namun arsitektur menyiapkannya (lihat dok. 02 §Extensibility dan dok. 06 Fase 5).
