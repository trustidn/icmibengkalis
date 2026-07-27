<?php

namespace App\Livewire\Admin\Gallery;

use App\Models\Album;
use App\Models\AlbumItem;
use App\Services\Gallery\GalleryService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public Album $album;

    /** Mode input foto: 'upload' (berkas) atau 'url' (tautan eksternal). */
    public string $photoMode = 'upload';

    public $photo = null;

    public string $photoUrl = '';

    public string $photoCaption = '';

    public string $videoUrl = '';

    public string $videoCaption = '';

    public function mount(Album $album): void
    {
        $this->authorize('update', $album);
        $this->album = $album;
    }

    public function addPhoto(GalleryService $gallery): void
    {
        $this->authorize('update', $this->album);

        if ($this->photoMode === 'url') {
            $this->validate(['photoUrl' => ['required', 'url', 'max:2048']]);

            try {
                $gallery->addPhotoFromUrl($this->album, $this->photoUrl, $this->photoCaption ?: null);
            } catch (\RuntimeException $e) {
                $this->addError('photoUrl', $e->getMessage());

                return;
            }

            $this->reset(['photoUrl', 'photoCaption']);

            return;
        }

        $this->validate(['photo' => ['required', 'image', 'max:5120']]);

        $gallery->addPhoto($this->album, $this->photo, $this->photoCaption ?: null);

        $this->reset(['photo', 'photoCaption']);
    }

    public function addVideo(GalleryService $gallery): void
    {
        $this->authorize('update', $this->album);
        $this->validate(['videoUrl' => ['required', 'url']]);

        try {
            $gallery->addVideo($this->album, $this->videoUrl, $this->videoCaption ?: null);
        } catch (\InvalidArgumentException $e) {
            $this->addError('videoUrl', $e->getMessage());

            return;
        }

        $this->reset(['videoUrl', 'videoCaption']);
    }

    public function removeItem(int $itemId, GalleryService $gallery): void
    {
        $item = AlbumItem::findOrFail($itemId);
        $this->authorize('update', $this->album);

        $gallery->removeItem($item);
    }

    public function render()
    {
        return view('livewire.admin.gallery.form', [
            'items' => $this->album->items()->with('media')->get(),
        ]);
    }
}
