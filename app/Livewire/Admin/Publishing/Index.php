<?php

namespace App\Livewire\Admin\Publishing;

use App\Models\Post;
use App\Services\Publishing\PublishingService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Post::class);
    }

    public function submitForReview(int $postId, PublishingService $publishing): void
    {
        $post = Post::findOrFail($postId);
        $this->authorize('update', $post);

        $publishing->submitForReview($post);
    }

    public function revise(int $postId, PublishingService $publishing): void
    {
        $post = Post::findOrFail($postId);
        $this->authorize('update', $post);

        $publishing->revise($post);
    }

    public function archive(int $postId, PublishingService $publishing): void
    {
        $post = Post::findOrFail($postId);
        $this->authorize('publish', $post);

        $publishing->archive($post);
    }

    public function delete(int $postId): void
    {
        $post = Post::findOrFail($postId);
        $this->authorize('delete', $post);

        $post->delete();
    }

    public function render()
    {
        return view('livewire.admin.publishing.index', [
            'posts' => Post::with(['author', 'category'])->latest()->paginate(15),
        ]);
    }
}
