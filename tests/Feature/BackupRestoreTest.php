<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_bisa_membuka_halaman_backup(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get(route('admin.backup.index'))
            ->assertOk()
            ->assertSee('Backup');
    }

    public function test_halaman_menampilkan_peringatan_di_luar_mysql(): void
    {
        // Suite test berjalan di SQLite — fitur harus tampil nonaktif dengan pesan jelas.
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get(route('admin.backup.index'))
            ->assertSee('MySQL/MariaDB');
    }

    public function test_peran_tanpa_settings_manage_ditolak(): void
    {
        $sekretaris = User::factory()->create();
        $sekretaris->assignRole('sekretaris');

        $this->actingAs($sekretaris)->get(route('admin.backup.index'))->assertForbidden();
        $this->actingAs($sekretaris)->get(route('admin.backup.download'))->assertForbidden();
    }

    public function test_unduhan_404_di_luar_mysql(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get(route('admin.backup.download'))
            ->assertNotFound();
    }

    public function test_guest_dialihkan_ke_login(): void
    {
        $this->get(route('admin.backup.index'))->assertRedirect();
    }
}
