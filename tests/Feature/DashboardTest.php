<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_menampilkan_pengingat_lengkapi_profil_bila_belum_lengkap(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('Lengkapi Profil Anda');
    }

    public function test_dashboard_tidak_menampilkan_pengingat_untuk_user_tanpa_member(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertDontSee('Lengkapi Profil Anda');
    }

    public function test_dashboard_menampilkan_jumlah_artikel_terbit_dan_milik_saya(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Post::factory()->published()->create(['author_id' => $user->id, 'title' => 'Artikel Saya Satu']);
        Post::factory()->published()->count(2)->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeText('Total Artikel Terbit')
            ->assertSeeText('3')
            ->assertSeeText('Artikel Saya')
            ->assertSeeText('1');
    }

    public function test_dashboard_menampilkan_pengumuman_terbaru(): void
    {
        $user = User::factory()->create();

        Announcement::factory()->create(['title' => 'Rapat Anggota Tahunan']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('Rapat Anggota Tahunan');
    }

    public function test_anggota_melihat_link_tulis_dan_kelola_artikel_sendiri(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee(route('member.posts.create'))
            ->assertSee(route('member.posts.index'))
            ->assertDontSee(route('admin.publishing.index'));
    }

    public function test_editor_melihat_link_kelola_semua_artikel(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo(['publishing.view', 'publishing.create']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee(route('admin.publishing.index'))
            ->assertSee(route('admin.publishing.create'));
    }
}
