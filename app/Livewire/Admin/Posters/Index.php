<?php

namespace App\Livewire\Admin\Posters;

use App\Models\Poster;
use App\Services\Content\PosterService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Kelola poster ucapan beranda (hari jadi, hari kemerdekaan, dll.).
 * Gate: pages.manage (pengelola konten situs).
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public string $title = '';

    public $image = null;

    public string $link_url = '';

    public string $starts_at = '';

    public string $ends_at = '';

    /** Bila terisi, form dalam mode ubah (bukan tambah). */
    public ?int $editingId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);
    }

    public function edit(int $posterId): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        $poster = Poster::findOrFail($posterId);

        $this->editingId = $poster->id;
        $this->title = $poster->title;
        $this->link_url = (string) $poster->link_url;
        $this->starts_at = $poster->starts_at?->format('Y-m-d') ?? '';
        $this->ends_at = $poster->ends_at?->format('Y-m-d') ?? '';
        $this->image = null;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'title', 'image', 'link_url', 'starts_at', 'ends_at']);
        $this->resetValidation();
    }

    public function save(PosterService $posters): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        // Saat mengubah, gambar boleh dikosongkan — gambar lama dipertahankan.
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => [$this->editingId ? 'nullable' : 'required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $data = [
            'title' => $validated['title'],
            'link_url' => $validated['link_url'] ?: null,
            'starts_at' => $validated['starts_at'] ?: null,
            'ends_at' => $validated['ends_at'] ?: null,
        ];

        if ($this->editingId) {
            $posters->update(Poster::findOrFail($this->editingId), $data, $this->image);
            $pesan = 'Perubahan poster tersimpan.';
        } else {
            $posters->create([...$data, 'is_active' => true], $this->image);
            $pesan = 'Poster tersimpan.';
        }

        $this->reset(['editingId', 'title', 'image', 'link_url', 'starts_at', 'ends_at']);

        session()->flash('posters.saved', $pesan);
    }

    public function toggleActive(int $posterId, PosterService $posters): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        $posters->toggleActive(Poster::findOrFail($posterId));
    }

    public function deletePoster(int $posterId, PosterService $posters): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        $posters->delete(Poster::findOrFail($posterId));
    }

    public function render()
    {
        return view('livewire.admin.posters.index', [
            'posters' => Poster::with('media')->latest()->get(),
        ]);
    }
}
