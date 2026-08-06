<?php

namespace App\Livewire\Public\Posts;

use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Apresiasi artikel — satu per orang per artikel. Anggota login dikenali
 * lewat akunnya; pengunjung lewat token cookie berumur setahun.
 */
class LikeButton extends Component
{
    public int $postId;

    public int $count = 0;

    public bool $liked = false;

    /** Token identitas pengunjung tanpa login — dipertahankan di state & cookie. */
    public string $guestToken = '';

    public function mount(Post $post): void
    {
        $this->postId = $post->id;

        $dariCookie = request()->cookie('apresiasi_token');
        $this->guestToken = is_string($dariCookie) && strlen($dariCookie) >= 20
            ? $dariCookie
            : Str::random(40);

        // Baca langsung dari DB — model $post bisa berasal dari cache 10 menit.
        $this->count = (int) DB::table('posts')->where('id', $post->id)->value('likes_count');
        $this->liked = PostLike::where('post_id', $post->id)
            ->where('liker_key', $this->likerKey())
            ->exists();
    }

    public function toggle(): void
    {
        // Rem pengaman terhadap klik beruntun/otomatis — bukan pengganti keunikan.
        if (! RateLimiter::attempt('post-like:'.request()->ip(), 30, fn () => true, 60)) {
            return;
        }

        $key = $this->likerKey();

        if (! auth()->check()) {
            Cookie::queue(cookie('apresiasi_token', $this->guestToken, 60 * 24 * 365));
        }

        $sudahAda = PostLike::where('post_id', $this->postId)->where('liker_key', $key)->first();

        if ($sudahAda) {
            $sudahAda->delete();
            DB::table('posts')->where('id', $this->postId)
                ->where('likes_count', '>', 0)->decrement('likes_count');
            $this->liked = false;
        } else {
            PostLike::firstOrCreate(['post_id' => $this->postId, 'liker_key' => $key]);
            DB::table('posts')->where('id', $this->postId)->increment('likes_count');
            $this->liked = true;
        }

        $this->count = (int) DB::table('posts')->where('id', $this->postId)->value('likes_count');
    }

    private function likerKey(): string
    {
        return auth()->check()
            ? 'user:'.auth()->id()
            : 'guest:'.$this->guestToken;
    }

    public function render()
    {
        return view('livewire.public.posts.like-button');
    }
}
