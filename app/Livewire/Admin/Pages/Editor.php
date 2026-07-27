<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use App\Services\Content\PageService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Editor extends Component
{
    use WithFileUploads;

    public ?int $selectedId = null;

    public string $title = '';

    public string $body = '';

    public $featured_image = null;

    public bool $saved = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Page::class);
    }

    public function select(int $pageId): void
    {
        $page = Page::findOrFail($pageId);
        $this->authorize('update', $page);

        $this->selectedId = $page->id;
        $this->title = $page->title;
        $this->body = (string) $page->body;
        $this->featured_image = null;
        $this->saved = false;
    }

    public function save(PageService $pages): void
    {
        $page = Page::findOrFail($this->selectedId);
        $this->authorize('update', $page);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        unset($validated['featured_image']);

        $pages->update($page, $validated, auth()->user());

        if ($this->featured_image) {
            $page->addMedia($this->featured_image->getRealPath())
                ->usingFileName('featured.'.$this->featured_image->getClientOriginalExtension())
                ->toMediaCollection('featured');
            $this->featured_image = null;
        }

        $this->saved = true;
    }

    public function removeFeaturedImage(): void
    {
        $page = Page::findOrFail($this->selectedId);
        $this->authorize('update', $page);

        $page->clearMediaCollection('featured');
    }

    public function render(PageService $pages)
    {
        return view('livewire.admin.pages.editor', [
            'pages' => $pages->all(),
        ]);
    }
}
