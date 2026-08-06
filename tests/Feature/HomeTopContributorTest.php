<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTopContributorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_beranda_menampilkan_top_kontributor_bulan_ini(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id, 'full_name' => 'Penulis Rajin', 'profession' => 'Dosen']);

        Post::factory()->published()->count(2)->create(['author_id' => $user->id, 'published_at' => now()->subDays(2)]);
        Post::factory()->published()->create(['author_id' => $user->id, 'published_at' => now()->subMonths(3)]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Top Kontributor Bulan Ini')
            ->assertSee('Penulis Rajin')
            ->assertSee('2 bulan ini')
            ->assertSee('3 total')
            ->assertSee(route('profiles.show', $member->slug));
    }

    public function test_super_admin_tidak_masuk_daftar_kontributor(): void
    {
        $super = User::factory()->create();
        $super->assignRole('super-admin');
        Member::factory()->create(['user_id' => $super->id, 'full_name' => 'Boss Superadmin', 'nia' => 'SA-0001']);
        Post::factory()->published()->create(['author_id' => $super->id, 'published_at' => now()->subDay()]);

        $biasa = User::factory()->create();
        Member::factory()->create(['user_id' => $biasa->id, 'full_name' => 'Kontributor Biasa', 'nia' => 'KB-0001']);
        Post::factory()->published()->create(['author_id' => $biasa->id, 'published_at' => now()->subDay()]);

        $content = $this->get(route('home'))->assertOk()->getContent();

        // Periksa hanya di dalam section kontributor — nama superadmin sah
        // muncul di tempat lain (mis. chip penulis pada kartu berita).
        $section = substr(
            $content,
            strpos($content, 'Top Kontributor Bulan Ini'),
            strpos($content, 'Agenda Mendatang') - strpos($content, 'Top Kontributor Bulan Ini')
        );

        $this->assertStringContainsString('Kontributor Biasa', $section);
        $this->assertStringNotContainsString('Boss Superadmin', $section);
    }

    public function test_section_tersembunyi_bila_tidak_ada_tulisan_bulan_ini(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id, 'nia' => 'K-0001']);
        Post::factory()->published()->create(['author_id' => $user->id, 'published_at' => now()->subMonths(2)]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Top Kontributor Bulan Ini');
    }
}
