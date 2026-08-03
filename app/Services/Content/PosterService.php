<?php

namespace App\Services\Content;

use App\Models\Poster;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class PosterService
{
    /** Poster yang sedang tayang — dipakai section beranda (kosong = section disembunyikan). */
    public function active(): Collection
    {
        return Cache::remember('public.posters.active', now()->addMinutes(5), function () {
            return Poster::query()->currentlyVisible()->with('media')->latest()->get();
        });
    }

    public function create(array $data, UploadedFile $image): Poster
    {
        $poster = Poster::create($data);
        $poster->addMedia($image->getRealPath())
            ->usingFileName('poster-'.$poster->id.'.'.$image->getClientOriginalExtension())
            ->toMediaCollection('image');

        $this->flushCache();

        return $poster;
    }

    /** Gambar hanya diganti bila berkas baru diunggah; bila null, gambar lama dipertahankan. */
    public function update(Poster $poster, array $data, ?UploadedFile $image = null): Poster
    {
        $poster->update($data);

        if ($image) {
            $poster->addMedia($image->getRealPath())
                ->usingFileName('poster-'.$poster->id.'.'.$image->getClientOriginalExtension())
                ->toMediaCollection('image');
        }

        $this->flushCache();

        return $poster->fresh();
    }

    public function toggleActive(Poster $poster): void
    {
        $poster->update(['is_active' => ! $poster->is_active]);
        $this->flushCache();
    }

    public function delete(Poster $poster): void
    {
        $poster->delete();
        $this->flushCache();
    }

    private function flushCache(): void
    {
        Cache::forget('public.posters.active');
    }
}
