<?php

namespace App\Livewire\Public\Posts;

use App\Services\Publishing\PublishingService;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Show extends Component
{
    public string $slug;

    public function mount(string $slug, PublishingService $publishing): void
    {
        $post = $publishing->findPublished($slug);

        abort_unless($post, Response::HTTP_NOT_FOUND);

        $this->slug = $slug;

        $publishing->incrementViewCount($post);
    }

    public function render(PublishingService $publishing)
    {
        $post = $publishing->findPublished($this->slug);

        return view('livewire.public.posts.show', ['post' => $post])
            ->layout('components.layouts.public', [
                'metaTitle' => $post->title.' — '.config('app.name'),
                'metaDescription' => $post->seo_meta['description'] ?? Str::limit(strip_tags($post->excerpt ?: $post->body), 160),
            ]);
    }
}
