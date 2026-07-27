<?php

namespace App\Livewire\Admin\Publishing;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Publishing\PublishingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ReviewQueue extends Component
{
    #[Url]
    public string $tab = 'menunggu';

    public ?int $rejectingId = null;

    public string $rejectNote = '';

    public string $scheduledAt = '';

    public function mount(): void
    {
        $this->authorize('review', Post::class);
    }

    public function approve(int $postId, PublishingService $publishing): void
    {
        $post = Post::findOrFail($postId);
        $this->authorize('review', Post::class);

        $publishing->approve($post, auth()->id());
    }

    public function schedule(int $postId, PublishingService $publishing): void
    {
        $post = Post::findOrFail($postId);
        $this->authorize('review', Post::class);

        $this->validate(['scheduledAt' => ['required', 'date', 'after:now']]);

        $publishing->approve($post, auth()->id(), $this->scheduledAt);

        $this->reset(['scheduledAt']);
    }

    public function startReject(int $postId): void
    {
        $this->rejectingId = $postId;
        $this->rejectNote = '';
    }

    public function reject(PublishingService $publishing): void
    {
        $this->authorize('review', Post::class);

        $validated = $this->validate(['rejectNote' => ['required', 'string', 'max:1000']]);

        $post = Post::findOrFail($this->rejectingId);
        $publishing->reject($post, auth()->id(), $validated['rejectNote']);

        $this->reset(['rejectingId', 'rejectNote']);
    }

    public function render()
    {
        $query = Post::with(['author', 'category'])->latest();

        $posts = match ($this->tab) {
            'terjadwal' => $query->where('status', PostStatus::Published)->where('published_at', '>', now())->get(),
            'ditolak' => $query->where('status', PostStatus::Rejected)->get(),
            default => $query->where('status', PostStatus::InReview)->get(),
        };

        return view('livewire.admin.publishing.review-queue', ['posts' => $posts]);
    }
}
