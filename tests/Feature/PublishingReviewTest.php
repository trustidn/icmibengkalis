<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Livewire\Admin\Publishing\ReviewQueue;
use App\Livewire\Member\Posts\Create as MemberPostsCreate;
use App\Models\Member;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublishingReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function reviewer(): User
    {
        $reviewer = User::factory()->create();
        $reviewer->givePermissionTo(['publishing.view', 'publishing.review']);

        return $reviewer;
    }

    public function test_post_in_review_muncul_di_tab_menunggu(): void
    {
        $post = Post::factory()->create(['status' => PostStatus::InReview, 'title' => 'Menunggu Review Ini']);

        Livewire::actingAs($this->reviewer())
            ->test(ReviewQueue::class)
            ->set('tab', 'menunggu')
            ->assertSee('Menunggu Review Ini');
    }

    public function test_approve_menerbitkan_dan_hilang_dari_antrean(): void
    {
        $reviewer = $this->reviewer();
        $post = Post::factory()->create(['status' => PostStatus::InReview]);

        Livewire::actingAs($reviewer)
            ->test(ReviewQueue::class)
            ->call('approve', $post->id);

        $post->refresh();
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertSame($reviewer->id, $post->reviewed_by);
    }

    public function test_approve_dengan_tanggal_masa_depan_tidak_tampil_di_publik(): void
    {
        $reviewer = $this->reviewer();
        $post = Post::factory()->create(['status' => PostStatus::InReview, 'title' => 'Post Terjadwal']);
        $future = now()->addWeek()->format('Y-m-d\TH:i');

        Livewire::actingAs($reviewer)
            ->test(ReviewQueue::class)
            ->set('scheduledAt', $future)
            ->call('schedule', $post->id)
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertTrue($post->published_at->isFuture());

        $this->get(route('posts.index'))->assertDontSee('Post Terjadwal');

        Livewire::actingAs($reviewer)
            ->test(ReviewQueue::class)
            ->set('tab', 'terjadwal')
            ->assertSee('Post Terjadwal');
    }

    public function test_reject_menyimpan_catatan_dan_penulis_bisa_revisi(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $author->givePermissionTo(['publishing.view', 'publishing.create', 'publishing.update']);
        $post = Post::factory()->create(['status' => PostStatus::InReview, 'author_id' => $author->id]);

        Livewire::actingAs($reviewer)
            ->test(ReviewQueue::class)
            ->call('startReject', $post->id)
            ->set('rejectNote', 'Perbaiki sumber data.')
            ->call('reject')
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertSame(PostStatus::Rejected, $post->status);
        $this->assertSame('Perbaiki sumber data.', $post->review_note);

        $this->assertTrue($author->can('update', $post));
    }

    public function test_anggota_submit_opini_langsung_in_review(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('title', 'Opini Saya')
            ->set('body', 'Isi opini yang cukup panjang untuk validasi.')
            ->call('submit')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Opini Saya')->first();
        $this->assertNotNull($post);
        $this->assertSame(PostStatus::InReview, $post->status);
        $this->assertSame(PostType::Opini, $post->type);
        $this->assertSame($user->id, $post->author_id);
    }

    public function test_anggota_submit_berita_langsung_terbit(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('type', 'berita')
            ->set('title', 'Berita Dari Anggota')
            ->set('body', 'Isi berita yang cukup panjang untuk validasi.')
            ->call('submit')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Berita Dari Anggota')->first();
        $this->assertNotNull($post);
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertSame(PostType::Berita, $post->type);
        $this->assertNotNull($post->published_at);
    }

    public function test_anggota_submit_artikel_langsung_terbit(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('type', 'artikel')
            ->set('title', 'Artikel Dari Anggota')
            ->set('body', 'Isi artikel yang cukup panjang untuk validasi.')
            ->call('submit')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Artikel Dari Anggota')->first();
        $this->assertSame(PostStatus::Published, $post->status);
    }

    public function test_anggota_tidak_bisa_submit_siaran_pers(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('type', 'press_release')
            ->set('title', 'Siaran Pers Ilegal')
            ->set('body', 'Isi yang cukup panjang untuk validasi.')
            ->call('submit')
            ->assertHasErrors(['type']);
    }

    public function test_berita_dari_anggota_muncul_di_halaman_publik(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('type', 'berita')
            ->set('title', 'Berita Publik Dari Anggota')
            ->set('body', 'Isi berita yang cukup panjang untuk validasi.')
            ->call('submit');

        $this->get(route('posts.index'))->assertSee('Berita Publik Dari Anggota');
    }

    public function test_user_tanpa_member_ditolak_menulis_opini(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('member.posts.create'))
            ->assertForbidden();
    }

    public function test_user_tanpa_permission_review_ditolak_akses_antrean(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.publishing.review-queue'))
            ->assertForbidden();
    }
}
