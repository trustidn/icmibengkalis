<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_publik_hanya_melihat_pengumuman_aktif(): void
    {
        Announcement::factory()->create(['title' => 'Aktif Sekarang', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        Announcement::factory()->create(['title' => 'Sudah Kedaluwarsa', 'starts_at' => now()->subMonth(), 'ends_at' => now()->subWeek()]);

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('Aktif Sekarang')
            ->assertDontSee('Sudah Kedaluwarsa');
    }

    public function test_user_tanpa_permission_ditolak_akses_admin_pengumuman(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.announcements.index'))
            ->assertForbidden();
    }

    public function test_admin_dengan_permission_bisa_mengakses_pengumuman(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('announcements.manage');

        $this->actingAs($admin)
            ->get(route('admin.announcements.index'))
            ->assertOk();
    }
}
