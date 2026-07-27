<?php

namespace App\Livewire\Public\Gallery;

use App\Services\Gallery\GalleryService;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Show extends Component
{
    public string $slug;

    public function mount(string $slug, GalleryService $gallery): void
    {
        $album = $gallery->findBySlug($slug);

        abort_unless($album, Response::HTTP_NOT_FOUND);

        $this->slug = $slug;
    }

    public function render(GalleryService $gallery)
    {
        $album = $gallery->findBySlug($this->slug);

        return view('livewire.public.gallery.show', ['album' => $album])
            ->layout('components.layouts.public', [
                'metaTitle' => $album->title.' — '.config('app.name'),
                'metaDescription' => Str::limit(strip_tags((string) $album->description), 160),
            ]);
    }
}
