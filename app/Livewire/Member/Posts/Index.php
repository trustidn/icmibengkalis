<?php

namespace App\Livewire\Member\Posts;

use App\Models\Post;
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

    /** Hapus hanya untuk pemegang hak (policy delete) — tombol pun tampil sesuai hak. */
    public function delete(int $postId): void
    {
        $post = Post::where('author_id', auth()->id())->findOrFail($postId);
        $this->authorize('delete', $post);

        $post->delete();
    }

    public function render()
    {
        $posts = Post::query()
            ->where('author_id', auth()->id())
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('livewire.member.posts.index', ['posts' => $posts]);
    }
}
