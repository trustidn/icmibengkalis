<?php

namespace Tests\Feature;

use App\Livewire\Member\Posts\Create as MemberPostsCreate;
use App\Models\Member;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\Publishing\PublishingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_ringkasan_otomatis_dari_kalimat_awal_paragraf_pertama(): void
    {
        $svc = app(PublishingService::class);

        $body = '<p>Kalimat pertama tentang ekonomi. Kalimat kedua menjelaskan detail. Kalimat ketiga tidak ikut.</p><p>Paragraf kedua diabaikan.</p>';

        $this->assertSame(
            'Kalimat pertama tentang ekonomi. Kalimat kedua menjelaskan detail.',
            $svc->makeExcerpt($body)
        );
    }

    public function test_tag_dari_kata_kunci_dipisah_koma(): void
    {
        $svc = app(PublishingService::class);

        $ids = $svc->tagIdsFromKeywords('ekonomi syariah, UMKM , ekonomi syariah,, pendidikan');

        $this->assertCount(3, $ids);
        $this->assertSame(3, Tag::count());
        $this->assertNotNull(Tag::where('name', 'UMKM')->first());
    }

    public function test_anggota_kirim_tulisan_dengan_tag_caption_dan_ringkasan_otomatis(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class)
            ->set('title', 'Tulisan Berkata Kunci')
            ->set('body', '<p>Ini kalimat pembuka yang menarik. Ini kalimat kedua pelengkap. Ini kalimat ketiga.</p>')
            ->set('tags', 'ekonomi syariah, umkm')
            ->set('featured_caption', 'Suasana kegiatan di aula')
            ->call('submit')
            ->assertHasNoErrors();

        $post = Post::where('title', 'Tulisan Berkata Kunci')->first();
        $this->assertSame('Ini kalimat pembuka yang menarik. Ini kalimat kedua pelengkap.', $post->excerpt);
        $this->assertSame('Suasana kegiatan di aula', $post->featured_caption);
        $this->assertSame(2, $post->tags()->count());
    }

    public function test_edit_memuat_tag_sebagai_teks_koma_dan_sinkron_ulang(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->published()->create(['author_id' => $user->id]);
        $post->tags()->sync([
            Tag::create(['name' => 'lama satu'])->id,
            Tag::create(['name' => 'lama dua'])->id,
        ]);

        Livewire::actingAs($user)
            ->test(MemberPostsCreate::class, ['post' => $post])
            ->assertSet('tags', 'lama satu, lama dua')
            ->set('tags', 'baru saja')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(['baru saja'], $post->fresh()->tags()->pluck('name')->all());
    }

    public function test_detail_artikel_menampilkan_hashtag_dan_share_berkonten(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Artikel Hashtag']);
        $post->tags()->sync([Tag::create(['name' => 'ekonomi syariah'])->id]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('#EkonomiSyariah')
            ->assertSee('hashtags=EkonomiSyariah', false)
            ->assertSee('Bagikan ke Instagram', false)
            ->assertSee('Bagikan ke TikTok', false);
    }
}
