<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Livewire\Admin\Publishing\Form as PublishingForm;
use App\Livewire\Member\Posts\Create as MemberPostsCreate;
use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function makeEditor(): User
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo(['publishing.view', 'publishing.create', 'publishing.update', 'publishing.publish']);

        return $editor;
    }

    public function test_admin_bisa_menentukan_tanggal_artikel_saat_membuat(): void
    {
        $editor = $this->makeEditor();

        Livewire::actingAs($editor)
            ->test(PublishingForm::class)
            ->set('title', 'Artikel Bertanggal')
            ->set('body', 'Isi artikel yang cukup panjang untuk validasi.')
            ->set('published_at', '2026-07-15')
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Artikel Bertanggal')->first();
        $this->assertNotNull($post);
        $this->assertSame('2026-07-15', $post->published_at->format('Y-m-d'));
    }

    public function test_admin_bisa_mengubah_tanggal_artikel_yang_sudah_terbit(): void
    {
        $editor = $this->makeEditor();
        $post = Post::factory()->published()->create(['author_id' => $editor->id]);

        Livewire::actingAs($editor)
            ->test(PublishingForm::class, ['post' => $post])
            ->set('published_at', '2026-01-05')
            ->call('save')
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertSame('2026-01-05', $post->published_at->format('Y-m-d'));
        $this->assertSame(PostStatus::Published, $post->status);
    }

    public function test_tanggal_kosong_tidak_menghapus_tanggal_yang_sudah_ada(): void
    {
        $editor = $this->makeEditor();
        $post = Post::factory()->published()->create(['author_id' => $editor->id]);
        $original = $post->published_at->format('Y-m-d');

        Livewire::actingAs($editor)
            ->test(PublishingForm::class, ['post' => $post])
            ->set('published_at', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($original, $post->refresh()->published_at->format('Y-m-d'));
    }

    public function test_anggota_bisa_menentukan_tanggal_artikel(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('title', 'Opini Bertanggal')
            ->set('body', 'Isi opini yang cukup panjang untuk validasi.')
            ->set('published_at', '2026-06-01')
            ->call('submit')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Opini Bertanggal')->first();
        $this->assertNotNull($post);
        $this->assertSame(PostStatus::Published, $post->status);
        // publishImmediately tidak boleh menimpa tanggal yang dipilih penulis.
        $this->assertSame('2026-06-01', $post->published_at->format('Y-m-d'));
    }

    public function test_anggota_tanpa_tanggal_terisi_otomatis_hari_ini(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('title', 'Opini Tanpa Tanggal')
            ->set('body', 'Isi opini yang cukup panjang untuk validasi.')
            ->call('submit')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Opini Tanpa Tanggal')->first();
        $this->assertSame(now()->format('Y-m-d'), $post->published_at->format('Y-m-d'));
    }

    public function test_daftar_publik_terurut_berdasarkan_tanggal_artikel(): void
    {
        $lama = Post::factory()->published()->create(['title' => 'Artikel Lama', 'published_at' => now()->subDays(10)]);
        $baru = Post::factory()->published()->create(['title' => 'Artikel Baru', 'published_at' => now()->subDay()]);

        $response = $this->get(route('posts.index'))->assertOk();

        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'Artikel Lama'),
            strpos($content, 'Artikel Baru'),
            'Artikel dengan tanggal lebih baru harus tampil lebih dulu.'
        );
    }
}
