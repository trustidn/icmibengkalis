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

        if (app()->environment('local')) {
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
