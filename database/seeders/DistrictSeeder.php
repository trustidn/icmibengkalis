<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** 11 kecamatan Kabupaten Bengkalis. */
class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Bengkalis', 'Bantan', 'Bukit Batu', 'Bandar Laksamana', 'Siak Kecil',
            'Rupat', 'Rupat Utara', 'Mandau', 'Bathin Solapan', 'Pinggir', 'Talang Muandau',
        ];

        foreach ($names as $name) {
            DB::table('districts')->updateOrInsert(['name' => $name], [
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
