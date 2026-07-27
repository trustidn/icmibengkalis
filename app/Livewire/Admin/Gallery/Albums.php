<?php

namespace App\Livewire\Admin\Gallery;

use App\Models\Album;
use App\Services\Gallery\GalleryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Albums extends Component
{
    public bool $showForm = false;

    public string $title = '';

    public string $type = 'foto';

    public string $description = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Album::class);
    }

    public function create(): void
    {
        $this->authorize('create', Album::class);
        $this->reset(['title', 'type', 'description']);
        $this->showForm = true;
    }

    public function save(GalleryService $gallery): void
    {
        $this->authorize('create', Album::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:foto,video'],
            'description' => ['nullable', 'string'],
        ]);

        $gallery->create([...$validated, 'created_by' => auth()->id()]);
        $this->showForm = false;
    }

    public function delete(int $albumId, GalleryService $gallery): void
    {
        $album = Album::findOrFail($albumId);
        $this->authorize('delete', $album);

        $gallery->delete($album);
    }

    public function togglePublish(int $albumId, GalleryService $gallery): void
    {
        $album = Album::findOrFail($albumId);
        $this->authorize('update', $album);

        $gallery->update($album, ['is_published' => ! $album->is_published]);
    }

    public function render(GalleryService $gallery)
    {
        return view('livewire.admin.gallery.albums', [
            'albums' => $gallery->paginateAll(),
        ]);
    }
}
