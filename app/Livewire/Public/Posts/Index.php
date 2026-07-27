<?php

namespace App\Livewire\Public\Posts;

use App\Models\PostCategory;
use App\Models\Tag;
use App\Services\Publishing\PublishingService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $category = '';

    #[Url]
    public string $tag = '';

    #[Url]
    public string $search = '';

    public function render(PublishingService $publishing)
    {
        return view('livewire.public.posts.index', [
            'posts' => $publishing->paginatePublished(
                categorySlug: $this->category ?: null,
                tagSlug: $this->tag ?: null,
                search: $this->search ?: null,
            ),
            'categories' => PostCategory::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Berita & Artikel — '.config('app.name'),
        ]);
    }
}
