<?php

namespace App\Livewire\Public\Gallery;

use App\Services\Gallery\GalleryService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render(GalleryService $gallery)
    {
        return view('livewire.public.gallery.index', [
            'albums' => $gallery->paginatePublished(),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Galeri — '.config('app.name'),
        ]);
    }
}
