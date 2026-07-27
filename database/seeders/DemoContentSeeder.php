<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Announcement;
use App\Models\District;
use App\Models\Document;
use App\Models\Event;
use App\Models\ExpertiseField;
use App\Models\Member;
use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use App\Models\Post;
use App\Models\Profession;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh khusus local agar halaman publik Fase 1 & 2 tidak kosong. Jangan pernah dijalankan di produksi.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'superadmin@demo.test')->first();

        if (! $author) {
            return;
        }

        if (Post::count() === 0) {
            Post::factory()->published()->count(3)->create(['author_id' => $author->id]);
        }

        if (Announcement::count() === 0) {
            Announcement::factory()->create([
                'title' => 'Selamat Datang di Portal ICMI Bengkalis',
                'body' => 'Portal resmi ICMI Orda Kabupaten Bengkalis kini hadir sebagai wadah informasi, silaturahmi, dan kolaborasi cendekiawan muslim. Jelajahi berita terkini, agenda kegiatan, galeri dokumentasi, serta profil organisasi kami.',
                'is_pinned' => true,
            ]);
        }

        if (Event::count() === 0) {
            Event::factory()->count(2)->create();
        }

        if (Member::count() === 0) {
            $bengkalis = District::where('name', 'Bengkalis')->first();
            $dosen = Profession::firstOrCreate(['name' => 'Dosen']);
            $ekonomi = ExpertiseField::where('name', 'Ekonomi')->first();

            $members = collect(range(1, 5))->map(fn () => Member::factory()->create([
                'district_id' => $bengkalis?->id,
                'profession_id' => $dosen->id,
                'profession' => $dosen->name,
            ]));

            if ($ekonomi) {
                $members->first()->expertises()->create([
                    'expertise_field_id' => $ekonomi->id,
                    'level' => 'pakar',
                ]);
            }

            $period = OrgPeriod::firstOrCreate(
                ['name' => '2025-2030'],
                ['starts_at' => '2025-01-01', 'ends_at' => '2030-01-01', 'is_active' => true]
            );

            $ketuaUnit = OrgUnit::firstOrCreate(
                ['org_period_id' => $period->id, 'name' => 'Ketua Umum'],
                ['sort_order' => 0]
            );

            $ketuaUnit->assignments()->firstOrCreate([
                'member_id' => $members[0]->id,
            ], [
                'position_title' => 'Ketua',
                'short_bio' => 'Menjabat sebagai Ketua ICMI Kabupaten Bengkalis periode 2025-2030.',
            ]);

            $bidangUnit = OrgUnit::firstOrCreate(
                ['org_period_id' => $period->id, 'name' => 'Bidang Ekonomi', 'parent_id' => $ketuaUnit->id],
                ['sort_order' => 1]
            );

            $bidangUnit->assignments()->firstOrCreate([
                'member_id' => $members[1]->id,
            ], [
                'position_title' => 'Ketua Bidang',
            ]);

            $memberUser = User::firstOrCreate(
                ['email' => 'anggota@demo.test'],
                ['name' => 'Anggota Demo', 'password' => 'password', 'email_verified_at' => now()],
            );
            $members->last()->update(['user_id' => $memberUser->id]);

            if (Post::where('type', PostType::Opini)->doesntExist()) {
                Post::factory()->create([
                    'type' => PostType::Opini,
                    'title' => 'Opini: Peran Cendekiawan dalam Digitalisasi Daerah',
                    'author_id' => $memberUser->id,
                    'status' => PostStatus::InReview,
                    'published_at' => null,
                ]);
            }
        }

        if (Document::count() === 0) {
            $samples = [
                ['title' => 'SK Kepengurusan Periode 2025-2030', 'doc_type' => 'sk', 'access_level' => 'publik'],
                ['title' => 'Notulen Rapat Pengurus Januari', 'doc_type' => 'notulen', 'access_level' => 'anggota'],
                ['title' => 'Laporan Keuangan Internal', 'doc_type' => 'lainnya', 'access_level' => 'terbatas'],
            ];

            foreach ($samples as $sample) {
                $document = Document::create([
                    ...$sample,
                    'uploaded_by' => $author->id,
                    'current_version' => 1,
                ]);

                $version = $document->versions()->create([
                    'version_number' => 1,
                    'uploaded_by' => $author->id,
                ]);

                $version->addMediaFromString("Dokumen contoh: {$sample['title']}")
                    ->usingFileName('dokumen.txt')
                    ->toMediaCollection('versions', 'local');
            }
        }
    }
}
