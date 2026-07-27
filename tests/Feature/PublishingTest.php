<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Livewire\Admin\Publishing\Index as PublishingIndex;
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
}
