<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users\Index;
use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
    }

    public function test_super_admin_bisa_membuka_manajemen_user(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Manajemen User');
    }

    public function test_peran_selain_super_admin_ditolak(): void
    {
        $sekretaris = User::factory()->create();
        $sekretaris->assignRole('sekretaris');

        $this->actingAs($sekretaris)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_web_tidak_bisa_akses_manajemen_user(): void
    {
        $adminWeb = User::factory()->create();
        $adminWeb->assignRole('admin-web');

        $this->actingAs($adminWeb)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_bisa_membuat_user_dengan_peran(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->set('name', 'Editor Baru')
            ->set('email', 'editor@contoh.test')
            ->set('password', 'rahasia-kuat')
            ->set('role', 'sekretaris')
            ->call('createUser')
            ->assertHasNoErrors();

        $user = User::where('email', 'editor@contoh.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('sekretaris'));
    }

    public function test_role_super_admin_tidak_bisa_diberikan_lewat_ui(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->set('name', 'Penyusup')
            ->set('email', 'penyusup@contoh.test')
            ->set('password', 'rahasia-kuat')
            ->set('role', 'super-admin')
            ->call('createUser')
            ->assertHasErrors('role');

        $this->assertNull(User::where('email', 'penyusup@contoh.test')->first());
    }

    public function test_akun_super_admin_tidak_bisa_dihapus(): void
    {
        $superLain = User::factory()->create();
        $superLain->assignRole('super-admin');

        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('deleteUser', $superLain->id)
            ->assertForbidden();

        $this->assertNotNull(User::find($superLain->id));
    }

    public function test_akun_super_admin_tidak_bisa_diubah_perannya(): void
    {
        $superLain = User::factory()->create();
        $superLain->assignRole('super-admin');

        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('startEditRole', $superLain->id)
            ->assertForbidden();

        $this->assertTrue($superLain->fresh()->hasRole('super-admin'));
    }

    public function test_tidak_bisa_menghapus_akun_sendiri(): void
    {
        // Super-admin menghapus dirinya sendiri tertahan (guard super-admin lebih dulu,
        // guard diri-sendiri sebagai lapisan kedua) — akun tetap ada.
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('deleteUser', $this->superAdmin->id)
            ->assertForbidden();

        $this->assertNotNull(User::find($this->superAdmin->id));
    }

    public function test_user_dengan_artikel_tidak_bisa_dihapus(): void
    {
        $penulis = User::factory()->create();
        $penulis->assignRole('anggota');
        Post::factory()->create(['author_id' => $penulis->id]);

        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('deleteUser', $penulis->id)
            ->assertHasErrors('protected');

        $this->assertNotNull(User::find($penulis->id));
    }

    public function test_hapus_user_melepas_tautan_anggota_tanpa_menghapus_anggota(): void
    {
        $user = User::factory()->create();
        $user->assignRole('anggota');
        $member = Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('deleteUser', $user->id)
            ->assertHasNoErrors();

        $this->assertNull(User::find($user->id));
        $this->assertNotNull($member->fresh());
        $this->assertNull($member->fresh()->user_id);
    }

    public function test_toggle_status_aktif_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('anggota');

        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('toggleActive', $user->id);

        $this->assertFalse($user->fresh()->is_active);

        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('toggleActive', $user->id);

        $this->assertTrue($user->fresh()->is_active);
    }
}
