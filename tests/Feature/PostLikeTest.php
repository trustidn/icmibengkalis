<?php

namespace Tests\Feature;

use App\Livewire\Public\Posts\LikeButton;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengunjung_bisa_memberi_dan_membatalkan_apresiasi(): void
    {
        $post = Post::factory()->published()->create();

        $komponen = Livewire::test(LikeButton::class, ['post' => $post])
            ->assertSet('liked', false)
            ->assertSet('count', 0)
            ->call('toggle')
            ->assertSet('liked', true)
            ->assertSet('count', 1);

        $this->assertSame(1, (int) $post->fresh()->likes_count);
        $this->assertSame(1, $post->likes()->count());

        $komponen->call('toggle')
            ->assertSet('liked', false)
            ->assertSet('count', 0);

        $this->assertSame(0, (int) $post->fresh()->likes_count);
        $this->assertSame(0, $post->likes()->count());
    }

    public function test_anggota_login_hanya_bisa_apresiasi_sekali(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        Livewire::actingAs($user)->test(LikeButton::class, ['post' => $post])->call('toggle');

        // Komponen baru (sesi berikutnya) tetap mengenali apresiasi user yang sama.
        Livewire::actingAs($user)->test(LikeButton::class, ['post' => $post])
            ->assertSet('liked', true)
            ->assertSet('count', 1);

        $this->assertSame(1, $post->likes()->count());
        $this->assertSame('user:'.$user->id, $post->likes()->first()->liker_key);
    }

    public function test_dua_user_berbeda_menghasilkan_dua_apresiasi(): void
    {
        $post = Post::factory()->published()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Livewire::actingAs($userA)->test(LikeButton::class, ['post' => $post])->call('toggle');
        Livewire::actingAs($userB)->test(LikeButton::class, ['post' => $post])
            ->assertSet('liked', false)
            ->call('toggle')
            ->assertSet('count', 2);

        $this->assertSame(2, (int) $post->fresh()->likes_count);
    }

    public function test_tombol_apresiasi_tampil_di_halaman_artikel(): void
    {
        $post = Post::factory()->published()->create();

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSeeLivewire(LikeButton::class)
            ->assertSee('Apresiasi');
    }

    public function test_jumlah_apresiasi_selalu_tampil_di_kartu_daftar_berita(): void
    {
        Post::factory()->published()->create();

        // Ikon jempol tampil meski jumlah apresiasi masih 0.
        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('thumb_up');
    }
}
