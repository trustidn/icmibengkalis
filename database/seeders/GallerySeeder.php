<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\User;
use App\Services\Gallery\GalleryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Data contoh galeri (album foto & video) — HANYA untuk pengembangan lokal,
 * TIDAK PERNAH dijalankan di produksi. Foto contoh diambil dari Lorem Picsum;
 * video contoh memakai film pendek resmi Blender Foundation di YouTube
 * (Creative Commons, aman ditanam sebagai contoh). Idempoten: dilewati total
 * bila album dengan judul-judul di bawah sudah pernah dibuat.
 */
class GallerySeeder extends Seeder
{
    public function run(): void
    {
        if (Album::where('title', 'Kegiatan Rapat Kerja Pengurus')->exists()) {
            $this->command?->info('GallerySeeder dilewati — data contoh sudah pernah dibuat.');

            return;
        }

        $author = User::where('email', 'superadmin@demo.test')->first();
        $gallery = app(GalleryService::class);

        $photoAlbums = [
            [
                'title' => 'Kegiatan Rapat Kerja Pengurus',
                'description' => 'Dokumentasi rapat kerja tahunan pengurus ICMI Kabupaten Bengkalis.',
                'photos' => 5,
            ],
            [
                'title' => 'Bakti Sosial Kesehatan Wilayah Pesisir',
                'description' => 'Dokumentasi kegiatan bakti sosial dan pemeriksaan kesehatan gratis bagi warga pesisir.',
                'photos' => 6,
            ],
            [
                'title' => 'Pelatihan Literasi Digital untuk UMKM',
                'description' => 'Dokumentasi pelatihan literasi digital dan pemasaran online bagi pelaku UMKM.',
                'photos' => 4,
            ],
        ];

        foreach ($photoAlbums as $data) {
            $album = $gallery->create([
                'title' => $data['title'],
                'type' => 'foto',
                'description' => $data['description'],
                'is_published' => true,
                'created_by' => $author?->id,
            ]);

            for ($i = 1; $i <= $data['photos']; $i++) {
                try {
                    $gallery->addPhotoFromUrl(
                        $album,
                        "https://picsum.photos/seed/icmi-gallery-{$album->id}-{$i}/1000/750",
                    );
                } catch (\Throwable $e) {
                    Log::warning("GallerySeeder: gagal unduh foto contoh untuk album {$album->title}: {$e->getMessage()}");
                }
            }
        }

        $videoAlbum = $gallery->create([
            'title' => 'Dokumentasi Video Kegiatan',
            'type' => 'video',
            'description' => 'Kumpulan video dokumentasi dan sambutan kegiatan ICMI Kabupaten Bengkalis.',
            'is_published' => true,
            'created_by' => $author?->id,
        ]);

        $videos = [
            ['url' => 'https://www.youtube.com/watch?v=YE7VzlLtp-4', 'caption' => 'Cuplikan dokumentasi kegiatan (contoh video 1)'],
            ['url' => 'https://www.youtube.com/watch?v=eRsGyueVLvQ', 'caption' => 'Cuplikan dokumentasi kegiatan (contoh video 2)'],
            ['url' => 'https://www.youtube.com/watch?v=TLkA0RELQ1g', 'caption' => 'Cuplikan dokumentasi kegiatan (contoh video 3)'],
        ];

        foreach ($videos as $video) {
            $gallery->addVideo($videoAlbum, $video['url'], $video['caption']);
        }

        $this->command?->info('GallerySeeder selesai: 3 album foto + 1 album video contoh dibuat.');
    }
}
