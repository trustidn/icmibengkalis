# 4. Diagram UML

Diagram dipilih untuk alur yang paling menentukan perilaku sistem. Semua dalam format Mermaid agar mudah diperbarui bersama kode.

---

## 4.1 Use Case Diagram (utama)

```mermaid
flowchart LR
    Publik((Publik))
    Anggota((Anggota))
    AdminDiv((Admin/Ketua Divisi))
    Sekretaris((Sekretaris/Editor))
    Ketua((Ketua))
    SA((Super Admin))

    subgraph Portal["Portal Digital ICMI Bengkalis"]
        UC1[Melihat konten publik:\nberita, agenda, galeri, pengumuman]
        UC2[Menelusuri struktur organisasi interaktif]
        UC3[Mencari pakar di direktori kepakaran]
        UC4[Mengajukan permintaan narasumber]
        UC5[Mendaftar kegiatan]
        UC6[Mengelola profil & klaim kepakaran]
        UC7[Mengirim opini/artikel]
        UC8[Mengunduh arsip sesuai akses]
        UC9[Mengelola konten & kegiatan divisi]
        UC10[Check-in peserta via QR]
        UC11[Mereview & mempublikasikan konten]
        UC12[Memverifikasi klaim kepakaran]
        UC13[Mengelola arsip & struktur organisasi]
        UC14[Melihat dashboard organisasi]
        UC15[Mengelola pengguna, peran & konfigurasi]
    end

    Publik --> UC1 & UC2 & UC3 & UC4 & UC5
    Anggota --> UC1 & UC5 & UC6 & UC7 & UC8
    AdminDiv --> UC9 & UC10 & UC14
    Sekretaris --> UC11 & UC12 & UC13 & UC14
    Ketua --> UC11 & UC14
    SA --> UC15
```

Catatan pewarisan aktor: Anggota mewarisi akses Publik; Admin Divisi mewarisi Anggota; Sekretaris/Ketua/Super Admin mewarisi Admin Divisi (lingkup penuh, bukan per divisi).

## 4.2 Activity Diagram — Workflow Publikasi (Draft → Review → Publish)

```mermaid
flowchart TD
    A([Mulai]) --> B[Penulis membuat konten\nstatus: Draft]
    B --> C{Siap diajukan?}
    C -- belum --> B
    C -- ya --> D[Ajukan review\nstatus: In Review]
    D --> E[Editor memeriksa konten]
    E --> F{Keputusan}
    F -- tolak --> G[Status: Rejected\n+ catatan revisi]
    G --> B
    F -- setujui --> H{Terjadwal?}
    H -- ya --> I[Simpan published_at\nmasa depan]
    I --> J[Scheduler mempublikasikan\npada waktunya]
    F -- "sunting dulu" --> E
    H -- tidak --> K[Status: Published]
    J --> K
    K --> L[Event PostPublished:\nbersihkan cache, notifikasi penulis]
    L --> M([Selesai])
```

## 4.3 Activity Diagram — Verifikasi Kepakaran

```mermaid
flowchart TD
    A([Mulai]) --> B[Anggota memilih bidang dari taksonomi]
    B --> C[Isi level, bukti, unggah lampiran,\nkesediaan narasumber]
    C --> D[Klaim tersimpan: status Diajukan]
    D --> E[Notifikasi ke verifikator]
    E --> F[Verifikator menilai bukti]
    F --> G{Valid?}
    G -- ya --> H[Status: Terverifikasi\ntampil di direktori publik]
    G -- tidak --> I[Status: Ditolak + alasan]
    I --> J{Anggota merevisi?}
    J -- ya --> C
    J -- tidak --> K([Selesai])
    H --> K
```

## 4.4 Sequence Diagram — QR Check-in Kegiatan

```mermaid
sequenceDiagram
    actor Petugas
    participant UI as Livewire CheckInPanel
    participant Svc as EventService
    participant DB as Database
    participant Q as Queue

    Petugas->>UI: Pindai QR (qr_token)
    UI->>Svc: checkIn(event, qrToken, petugas)
    Svc->>DB: cari registrasi by token (lock)
    alt token tidak ditemukan / beda event
        Svc-->>UI: GALAT: registrasi tidak valid
    else sudah check-in
        Svc-->>UI: PERINGATAN: sudah hadir pada {waktu}
    else valid
        Svc->>DB: update status=hadir, checked_in_at=now
        Svc->>Q: event ParticipantCheckedIn
        Svc-->>UI: SUKSES + nama peserta
        UI-->>Petugas: Tampilkan konfirmasi + hitungan hadir
    end
```

## 4.5 Sequence Diagram — Unduh Arsip Terproteksi

```mermaid
sequenceDiagram
    actor U as Pengguna
    participant R as Route /arsip/{doc}/unduh
    participant P as DocumentPolicy
    participant S as ArchiveService
    participant M as MediaLibrary/Storage

    U->>R: GET unduh (versi terbaru)
    R->>P: authorize view(user, document)
    alt akses ditolak
        P-->>U: 403
    else diizinkan
        R->>S: download(document, version?)
        S->>M: ambil file versi (disk private)
        S->>S: catat activity_log (unduh)
        M-->>U: StreamedResponse (file)
    end
```

## 4.6 Class Diagram — Domain Inti (disederhanakan)

```mermaid
classDiagram
    class User {
        +name: string
        +email: string
        +roles: Role[]
        +member(): Member
    }
    class Member {
        +nia: string
        +fullName: string
        +status: MemberStatus
        +expertises(): MemberExpertise[]
        +assignments(): OrgAssignment[]
    }
    class MemberExpertise {
        +level: ExpertiseLevel
        +status: VerificationStatus
        +verify(User): void
        +reject(User, reason): void
    }
    class ExpertiseField {
        +name: string
        +parent: ExpertiseField
        +children: ExpertiseField[]
    }
    class OrgPeriod {
        +name: string
        +isActive: bool
    }
    class OrgUnit {
        +name: string
        +parent: OrgUnit
        +assignments: OrgAssignment[]
    }
    class OrgAssignment {
        +positionTitle: string
    }
    class Post {
        +type: PostType
        +status: PostStatus
        +submitForReview(): void
        +publish(User): void
        +reject(User, note): void
    }
    class Document {
        +docType: DocType
        +accessLevel: AccessLevel
        +addVersion(file, note): DocumentVersion
    }
    class DocumentVersion {
        +versionNumber: int
    }
    class Event {
        +startsAt: DateTime
        +register(data): EventRegistration
    }
    class EventRegistration {
        +qrToken: string
        +checkIn(User): void
    }

    User "1" -- "0..1" Member
    Member "1" -- "*" MemberExpertise
    ExpertiseField "1" -- "*" MemberExpertise
    ExpertiseField "0..1" -- "*" ExpertiseField : parent
    OrgPeriod "1" -- "*" OrgUnit
    OrgUnit "0..1" -- "*" OrgUnit : parent
    OrgUnit "1" -- "*" OrgAssignment
    Member "1" -- "*" OrgAssignment
    User "1" -- "*" Post : author
    Document "1" -- "*" DocumentVersion
    Event "1" -- "*" EventRegistration
    Member "0..1" -- "*" EventRegistration
```

### Service layer (kontrak utama)

```mermaid
classDiagram
    class MemberService {
        +register(dto): Member
        +updateProfile(Member, dto): Member
        +changeStatus(Member, status, reason): void
        +import(file): ImportResult
    }
    class ExpertiseService {
        +claim(Member, dto): MemberExpertise
        +verify(claim, verifier): void
        +searchExperts(criteria): Paginator
    }
    class PublishingService {
        +submitForReview(Post): void
        +approve(Post, editor): void
        +reject(Post, editor, note): void
    }
    class ArchiveService {
        +store(dto, file): Document
        +addVersion(Document, file, note): DocumentVersion
        +download(Document, version): StreamedResponse
    }
    class EventService {
        +register(Event, dto): EventRegistration
        +checkIn(Event, qrToken, officer): CheckInResult
        +generateCertificates(Event): Batch
    }
```
