<?php

namespace App\Livewire\Admin\Publishing;

use App\Enums\PostType;
use App\Models\Post;
use App\Services\Publishing\PublishingService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    public $featured_image = null;

    public string $featured_caption = '';

    public string $type = 'berita';

    public string $title = '';

    public string $body = '';

    /** Kata kunci dipisah koma — jadi tag artikel & hashtag saat share. */
    public string $tags = '';

    public ?string $published_at = null;

    public function mount(?Post $post = null): void
    {
        if ($post?->exists) {
            $this->authorize('update', $post);
            $this->post = $post;
            $this->type = $post->type->value;
            $this->title = $post->title;
            $this->body = $post->body;
            $this->featured_caption = (string) $post->featured_caption;
            $this->published_at = $post->published_at?->format('Y-m-d');
            $this->tags = $post->tags()->pluck('name')->implode(', ');
        } else {
            $this->authorize('create', Post::class);
        }
    }

    public function save(PublishingService $publishing): void
    {
        $validated = $this->validate([
            'type' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, PostType::cases()))],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'tags' => ['nullable', 'string', 'max:500'],
            'featured_caption' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        unset($validated['featured_image'], $validated['tags']);
        $validated['featured_caption'] = $validated['featured_caption'] ?: null;

        // MariaDB strict menolak '' untuk kolom tanggal; kosong berarti
        // "jangan ubah" — tanggal terisi otomatis saat terbit jika belum ada.
        if (blank($validated['published_at'] ?? null)) {
            unset($validated['published_at']);
        }

        $tagIds = $publishing->tagIdsFromKeywords($this->tags);

        if ($this->post) {
            $publishing->update($this->post, $validated, $tagIds, auth()->id());
        } else {
            $this->post = $publishing->create($validated, auth()->id(), $tagIds);
        }

        if ($this->featured_image) {
            $this->post->addMedia($this->featured_image->getRealPath())
                ->usingFileName('featured.'.$this->featured_image->getClientOriginalExtension())
                ->toMediaCollection('featured');
            $this->featured_image = null;
        }

        $this->redirectRoute('admin.publishing.index', navigate: true);
    }

    public function removeFeaturedImage(): void
    {
        if ($this->post) {
            $this->authorize('update', $this->post);
            $this->post->clearMediaCollection('featured');
        }
    }

    public function submitForReview(PublishingService $publishing): void
    {
        if ($this->post?->status->value === 'rejected') {
            $publishing->revise($this->post);
        }

        $this->save($publishing);

        $publishing->submitForReview($this->post);
    }

    public function render()
    {
        return view('livewire.admin.publishing.form', [
            'types' => PostType::cases(),
            'revisions' => $this->post?->revisions()->with('editor')->get() ?? collect(),
        ]);
    }
}
