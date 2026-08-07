<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Livewire\Admin\Publishing\Index as PublishingIndex;
use App\Livewire\Public\Posts\Index;
use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_index_publik_hanya_menampilkan_post_published(): void
    {
        Post::factory()->published()->create(['title' => 'Berita Terbit']);
        Post::factory()->create(['title' => 'Draf Tersembunyi', 'status' => PostStatus::Draft]);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('Berita Terbit')
            ->assertDontSee('Draf Tersembunyi');
    }

    public function test_filter_jenis_menyaring_daftar_berita(): void
    {
        Post::factory()->published()->create(['title' => 'Berita Harian Kita', 'type' => 'berita']);
        Post::factory()->published()->create(['title' => 'Opini Cendekia Kita', 'type' => 'opini']);

        Livewire::test(Index::class)
            ->assertSee('Berita Harian Kita')
            ->assertSee('Opini Cendekia Kita')
            ->set('type', 'opini')
            ->assertSee('Opini Cendekia Kita')
            ->assertDontSee('Berita Harian Kita');
    }

    public function test_halaman_artikel_menampilkan_tombol_bagikan(): void
    {
        $post = Post::factory()->published()->create();

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('facebook.com/sharer', false)
            ->assertSee('twitter.com/intent/tweet', false)
            ->assertSee('wa.me', false)
            ->assertSee('Salin Tautan');
    }

    public function test_show_publik_404_untuk_post_belum_terbit(): void
    {
        $post = Post::factory()->create(['status' => PostStatus::Draft]);

        $this->get(route('posts.show', $post->slug))->assertNotFound();
    }

    public function test_penulis_bisa_mengajukan_review_draft_miliknya(): void
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo(['publishing.view', 'publishing.create', 'publishing.update', 'publishing.publish', 'publishing.delete']);
        $post = Post::factory()->create(['author_id' => $editor->id, 'status' => PostStatus::Draft]);

        Livewire::actingAs($editor)
            ->test(PublishingIndex::class)
            ->call('submitForReview', $post->id);

        $this->assertSame(PostStatus::InReview, $post->fresh()->status);
    }

    public function test_user_tanpa_permission_ditolak_akses_admin_publishing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.publishing.index'))
            ->assertForbidden();
    }

    public function test_tombol_edit_artikel_tampil_sesuai_hak_akses(): void
    {
        $author = User::factory()->create();
        Member::factory()->create(['user_id' => $author->id]);
        $post = Post::factory()->published()->create(['author_id' => $author->id]);

        // Tamu: tidak ada tombol edit.
        $this->get(route('posts.show', $post->slug))->assertDontSee('Edit Artikel');

        // Penulis: tombol edit ke halaman edit anggota.
        $this->actingAs($author)
            ->get(route('posts.show', $post->slug))
            ->assertSee('Edit Artikel')
            ->assertSee(route('member.posts.edit', $post), false);

        // Admin publishing: tombol edit ke halaman edit admin.
        $editor = User::factory()->create();
        $editor->givePermissionTo('publishing.update');
        $this->actingAs($editor)
            ->get(route('posts.show', $post->slug))
            ->assertSee('Edit Artikel')
            ->assertSee(route('admin.publishing.edit', $post), false);

        // User biasa bukan penulis: tidak ada tombol.
        $lain = User::factory()->create();
        Member::factory()->create(['user_id' => $lain->id]);
        $this->actingAs($lain)
            ->get(route('posts.show', $post->slug))
            ->assertDontSee('Edit Artikel');
    }
}
