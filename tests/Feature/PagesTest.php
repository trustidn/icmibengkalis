<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_public_dapat_melihat_halaman_statis(): void
    {
        $page = Page::factory()->create(['slug' => 'tentang', 'title' => 'Tentang Kami']);

        $this->get("/{$page->slug}")
            ->assertOk()
            ->assertSee('Tentang Kami');
    }

    public function test_halaman_yang_tidak_ada_mengembalikan_404(): void
    {
        $this->get('/halaman-tidak-ada')->assertNotFound();
    }

    public function test_admin_dengan_permission_pages_manage_bisa_mengakses_editor(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('pages.manage');
        Page::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.pages.index'))
            ->assertOk();
    }

    public function test_user_tanpa_permission_pages_manage_ditolak_akses_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.pages.index'))
            ->assertForbidden();
    }
}
