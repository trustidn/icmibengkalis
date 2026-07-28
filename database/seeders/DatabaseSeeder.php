<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DistrictSeeder::class,
            StaticPageSeeder::class,
            ExpertiseFieldSeeder::class,
            SiteSettingSeeder::class,
        ]);

        // Data demo: selalu di local; di environment lain hanya bila DEMO_SEED=true
        // (config('app.demo_seed') — via config, bukan env(), agar aman saat config:cache).
        if (app()->environment('local') || config('app.demo_seed')) {
            User::firstOrCreate(
                ['email' => 'superadmin@demo.test'],
                ['name' => 'Super Admin', 'password' => 'password', 'email_verified_at' => now()],
            )->syncRoles('super-admin');

            $this->call([
                DummySeeder::class,
                GallerySeeder::class,
                DemoContentSeeder::class,
            ]);
        }
    }
}
