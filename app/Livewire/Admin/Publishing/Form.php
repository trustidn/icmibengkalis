<?php

namespace App\Livewire\Admin\Publishing;

use App\Enums\PostType;
use App\Models\OrgUnit;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
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

    public string $type = 'berita';

    public string $title = '';

    public string $excerpt = '';

    public string $body = '';

    public ?int $post_category_id = null;

    public ?int $org_unit_id = null;

    /** @var array<int> */
    public array $selectedTags = [];

    public function mount(?Post $post = null): void
    {
        if ($post?->exists) {
            $this->authorize('update', $post);
            $this->post = $post;
            $this->type = $post->type->value;
            $this->title = $post->title;
            $this->excerpt = (string) $post->excerpt;
            $this->body = $post->body;
            $this->post_category_id = $post->post_category_id;
            $this->org_unit_id = $post->org_unit_id;
            $this->selectedTags = $post->tags()->pluck('tags.id')->all();
        } else {
            $this->authorize('create', Post::class);
        }
    }

    public function save(PublishingService $publishing): void
    {
        $validated = $this->validate([
            'type' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, PostType::cases()))],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'post_category_id' => ['nullable', 'exists:post_categories,id'],
            'org_unit_id' => ['nullable', 'exists:org_units,id'],
            'featured_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        unset($validated['featured_image']);

        if ($this->post) {
            $publishing->update($this->post, $validated, $this->selectedTags, auth()->id());
        } else {
            $this->post = $publishing->create($validated, auth()->id(), $this->selectedTags);
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
            'categories' => PostCategory::orderBy('name')->get(),
            'orgUnits' => OrgUnit::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'revisions' => $this->post?->revisions()->with('editor')->get() ?? collect(),
        ]);
    }
}
