<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Livewire\Member\Posts\Create as MemberPostsCreate;
use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_anggota_hanya_melihat_artikelnya_sendiri_di_daftar(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $other = User::factory()->create();

        Post::factory()->create(['author_id' => $user->id, 'title' => 'Tulisan Saya']);
        Post::factory()->create(['author_id' => $other->id, 'title' => 'Tulisan Orang Lain']);

        $this->actingAs($user)
            ->get(route('member.posts.index'))
            ->assertOk()
            ->assertSee('Tulisan Saya')
            ->assertDontSee('Tulisan Orang Lain');
    }

    public function test_user_tanpa_member_ditolak_akses_daftar_artikel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('member.posts.index'))
            ->assertForbidden();
    }

    public function test_anggota_bisa_ubah_opini_miliknya_yang_ditolak(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->create([
            'author_id' => $user->id,
            'type' => 'opini',
            'status' => PostStatus::Rejected,
            'review_note' => 'Perbaiki data.',
            'title' => 'Opini Lama',
        ]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class, ['post' => $post])
            ->assertSet('title', 'Opini Lama')
            ->set('title', 'Opini Sudah Diperbaiki')
            ->set('body', 'Isi opini yang sudah direvisi dan cukup panjang.')
            ->call('submit')
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertSame('Opini Sudah Diperbaiki', $post->title);
        // Kebijakan baru: revisi opini yang ditolak langsung tayang tanpa antre review lagi.
        $this->assertSame(PostStatus::Published, $post->status);
    }

    public function test_anggota_tidak_bisa_ubah_artikel_orang_lain(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $other = User::factory()->create();
        $post = Post::factory()->create([
            'author_id' => $other->id,
            'status' => PostStatus::Rejected,
        ]);

        $this->actingAs($user)
            ->get(route('member.posts.edit', $post))
            ->assertForbidden();
    }

    public function test_halaman_edit_terbuka_untuk_artikel_sendiri_yang_sudah_terbit(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->published()->create(['author_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('member.posts.edit', $post))
            ->assertOk();
    }

    public function test_tombol_ubah_tampil_untuk_artikel_terbit_maupun_ditolak(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        $rejected = Post::factory()->create(['author_id' => $user->id, 'status' => PostStatus::Rejected, 'title' => 'Bisa Diubah']);
        $published = Post::factory()->published()->create(['author_id' => $user->id, 'title' => 'Terbit Juga Bisa Diubah']);

        $this->actingAs($user)
            ->get(route('member.posts.index'))
            ->assertSee(route('member.posts.edit', $rejected))
            ->assertSee(route('member.posts.edit', $published));
    }

    public function test_anggota_bisa_mengedit_artikelnya_yang_sudah_tayang(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->published()->create([
            'author_id' => $user->id,
            'type' => 'opini',
            'title' => 'Opini Sudah Tayang',
        ]);

        $this->assertTrue($user->can('update', $post));

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class, ['post' => $post])
            ->assertSet('title', 'Opini Sudah Tayang')
            ->set('title', 'Opini Tayang Yang Disunting')
            ->set('body', 'Isi baru yang cukup panjang untuk validasi.')
            ->call('submit')
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertSame('Opini Tayang Yang Disunting', $post->title);
        $this->assertSame(PostStatus::Published, $post->status);
    }

    public function test_anggota_tetap_tidak_bisa_mengedit_artikel_tayang_orang_lain(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $lain = User::factory()->create();
        $post = Post::factory()->published()->create(['author_id' => $lain->id]);

        $this->assertFalse($user->can('update', $post));
    }
}
