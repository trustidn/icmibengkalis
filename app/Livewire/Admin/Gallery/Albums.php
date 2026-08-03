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

    public ?int $editingId = null;

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
        $this->reset(['title', 'type', 'description', 'editingId']);
        $this->showForm = true;
    }

    public function edit(int $albumId): void
    {
        $album = Album::findOrFail($albumId);
        $this->authorize('update', $album);

        $this->editingId = $album->id;
        $this->title = $album->title;
        $this->type = $album->type;
        $this->description = (string) $album->description;
        $this->showForm = true;
    }

    public function cancelEdit(): void
    {
        $this->reset(['title', 'type', 'description', 'editingId']);
        $this->showForm = false;
    }

    public function save(GalleryService $gallery): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:foto,video'],
            'description' => ['nullable', 'string'],
        ]);

        if ($this->editingId) {
            $album = Album::findOrFail($this->editingId);
            $this->authorize('update', $album);

            // Jenis album tidak diubah saat edit — item yang sudah ada mengikuti jenis lama.
            $gallery->update($album, ['title' => $validated['title'], 'description' => $validated['description']]);
        } else {
            $this->authorize('create', Album::class);
            $gallery->create([...$validated, 'created_by' => auth()->id()]);
        }

        $this->cancelEdit();
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
