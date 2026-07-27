<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Nama penulis pada artikel & anggota tertaut ke profil publik (bila anggota berstatus Aktif). */
class MemberProfileLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_byline_penulis_bertaut_ke_profil_jika_anggota_aktif(): void
    {
        $member = Member::factory()->create(['full_name' => 'Rina Marlina', 'status' => MemberStatus::Aktif]);
        $user = User::factory()->create(['name' => 'Rina Marlina']);
        $member->update(['user_id' => $user->id]);

        $post = Post::factory()->published()->create(['author_id' => $user->id]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee(route('profiles.show', $member->slug));
    }

    public function test_byline_penulis_teks_biasa_jika_penulis_tanpa_akun_anggota(): void
    {
        $user = User::factory()->create(['name' => 'Penulis Tanpa Anggota']);
        $post = Post::factory()->published()->create(['author_id' => $user->id]);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk()->assertSee('Penulis Tanpa Anggota');
        $response->assertDontSee('/profil/');
    }

    public function test_byline_penulis_teks_biasa_jika_anggota_tidak_aktif(): void
    {
        $member = Member::factory()->create(['full_name' => 'Anggota Nonaktif', 'status' => MemberStatus::TidakAktif]);
        $user = User::factory()->create(['name' => 'Anggota Nonaktif']);
        $member->update(['user_id' => $user->id]);

        $post = Post::factory()->published()->create(['author_id' => $user->id]);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk()->assertDontSee(route('profiles.show', $member->slug));
    }

    public function test_dashboard_menampilkan_tautan_profil_publik_untuk_anggota_aktif(): void
    {
        $member = Member::factory()->create(['status' => MemberStatus::Aktif]);
        $user = User::factory()->create();
        $member->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lihat Profil Publik')
            ->assertSee(route('profiles.show', $member->slug));
    }

    public function test_dashboard_tidak_menampilkan_tautan_profil_untuk_anggota_tidak_aktif(): void
    {
        $member = Member::factory()->create(['status' => MemberStatus::TidakAktif]);
        $user = User::factory()->create();
        $member->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Lihat Profil Publik');
    }
}
