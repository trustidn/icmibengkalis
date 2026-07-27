<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_render_dan_memuat_berita_terbaru(): void
    {
        Post::factory()->published()->create(['title' => 'Berita Utama']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Berita Utama');
    }

    public function test_kunjungan_beranda_tercatat(): void
    {
        $this->get('/');

        $this->assertDatabaseHas('page_views', [
            'path' => '/',
            'count' => 1,
        ]);

        $this->get('/');

        $this->assertDatabaseHas('page_views', [
            'path' => '/',
            'count' => 2,
        ]);
    }
}
