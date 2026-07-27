<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/** Baris konfigurasi web default. Idempoten: hanya membuat bila belum ada. */
class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::firstOrCreate([], [
            'site_name' => 'ICMI Kabupaten Bengkalis',
            'tagline' => 'Mewujudkan masyarakat Indonesia yang beradab dan berkeadilan melalui peran aktif cendekiawan muslim.',
            'address' => 'Kabupaten Bengkalis, Provinsi Riau, Indonesia.',
            'email' => 'info@icmibengkalis.or.id',
            'registration_enabled' => true,
        ]);
    }
}
