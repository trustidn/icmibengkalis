<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_menyertakan_post_yang_terbit(): void
    {
        $post = Post::factory()->published()->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8')
            ->assertSee(route('posts.show', $post->slug), false);
    }

    public function test_sitemap_menyertakan_halaman_indeks_publik(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('posts.index'), false)
            ->assertSee(route('gallery.index'), false)
            ->assertSee(route('agenda.index'), false)
            ->assertSee(route('contact.show'), false);
    }

    public function test_sitemap_tidak_menyertakan_artikel_terjadwal(): void
    {
        $scheduled = Post::factory()->published()->create(['published_at' => now()->addWeek()]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('posts.show', $scheduled->slug), false);
    }

    public function test_robots_txt_menunjuk_sitemap_dengan_url_absolut(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')
            ->assertSee(route('sitemap'));
    }
}
