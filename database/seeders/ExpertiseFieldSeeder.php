<?php

namespace Database\Seeders;

use App\Models\ExpertiseField;
use Illuminate\Database\Seeder;

/**
 * Taksonomi awal bidang keahlian (dipakai lagi oleh Direktori Kepakaran Fase 4). Idempoten.
 */
class ExpertiseFieldSeeder extends Seeder
{
    private array $taxonomy = [
        'Ekonomi' => ['Ekonomi Syariah', 'UMKM'],
        'Pendidikan' => ['Pendidikan Tinggi', 'Pendidikan Karakter'],
        'Teknologi' => ['Teknologi Informasi', 'Rekayasa Perangkat Lunak'],
        'Kesehatan' => ['Kesehatan Masyarakat', 'Gizi'],
    ];

    public function run(): void
    {
        foreach ($this->taxonomy as $rootName => $children) {
            $root = ExpertiseField::firstOrCreate(['name' => $rootName]);

            foreach ($children as $childName) {
                ExpertiseField::firstOrCreate(['name' => $childName], ['parent_id' => $root->id]);
            }
        }
    }
}
