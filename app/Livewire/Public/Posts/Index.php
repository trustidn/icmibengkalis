<?php

namespace App\Livewire\Public\Posts;

use App\Enums\PostType;
use App\Models\Tag;
use App\Services\Publishing\PublishingService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $type = '';

    #[Url]
    public string $tag = '';

    #[Url]
    public string $search = '';

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingTag(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(PublishingService $publishing)
    {
        return view('livewire.public.posts.index', [
            'posts' => $publishing->paginatePublished(
                tagSlug: $this->tag ?: null,
                search: $this->search ?: null,
                type: $this->type ?: null,
            ),
            'types' => PostType::cases(),
            'tags' => Tag::orderBy('name')->get(),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Berita & Artikel — '.config('app.name'),
        ]);
    }
}
