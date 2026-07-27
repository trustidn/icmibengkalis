<?php

namespace App\Services\Gallery;

use App\Models\Album;
use App\Models\AlbumItem;
use App\Support\ExternalImageUrl;
use App\Support\VideoUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GalleryService
{
    public function paginatePublished(int $perPage = 12): LengthAwarePaginator
    {
        return Album::query()->where('is_published', true)->with('items.media')->latest()->paginate($perPage);
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Album::query()->latest()->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Album
    {
        return Cache::remember("public.album.{$slug}", now()->addMinutes(10), function () use ($slug) {
            return Album::where('slug', $slug)->where('is_published', true)->with('items.media')->first();
        });
    }

    /** Item (foto/video) terbaru lintas album yang tayang — dipakai beranda. */
    public function latestItems(int $limit = 6): Collection
    {
        return Cache::remember("public.gallery.latest_items.{$limit}", now()->addMinutes(5), function () use ($limit) {
            return AlbumItem::query()
                ->whereHas('album', fn ($query) => $query->where('is_published', true))
                ->with(['album', 'media'])
                ->latest()
                ->limit($limit)
                ->get();
        });
    }

    public function create(array $data): Album
    {
        return Album::create($data);
    }

    public function update(Album $album, array $data): Album
    {
        $album->update($data);
        $this->flushCache($album->slug);

        return $album;
    }

    public function delete(Album $album): void
    {
        $this->flushCache($album->slug);
        $album->delete();
    }

    public function addPhoto(Album $album, UploadedFile $file, ?string $caption = null): AlbumItem
    {
        $item = $album->items()->create(['caption' => $caption, 'sort_order' => $this->nextSortOrder($album)]);
        $item->addMedia($file)->toMediaCollection('photo');

        $this->flushCache($album->slug);

        return $item;
    }

    /**
     * Tambah foto dari URL eksternal (Google Drive, Dropbox, Flickr, atau tautan
     * gambar langsung dari Instagram/Facebook dsb.) — diunduh dan disimpan
     * permanen di server kita agar tidak rusak bila tautan asal kedaluwarsa.
     *
     * @throws \RuntimeException bila URL tidak bisa diunduh sebagai gambar.
     */
    public function addPhotoFromUrl(Album $album, string $url, ?string $caption = null): AlbumItem
    {
        $normalized = ExternalImageUrl::normalize($url);

        $item = $album->items()->create(['caption' => $caption, 'sort_order' => $this->nextSortOrder($album)]);

        try {
            $item->addMediaFromUrl($normalized)->toMediaCollection('photo');
        } catch (\Throwable $e) {
            $item->delete();

            throw new \RuntimeException(
                'Gagal mengunduh gambar dari URL tersebut. Pastikan tautan mengarah langsung ke berkas gambar (bukan halaman pratinjau).',
                previous: $e
            );
        }

        $this->flushCache($album->slug);

        return $item;
    }

    /**
     * Tambah video dari URL YouTube atau Vimeo. Thumbnail YouTube diturunkan
     * langsung dari pola URL (tanpa panggilan HTTP); thumbnail Vimeo diambil
     * sekali via oEmbed saat video ditambahkan (gagal dengan aman ke null bila
     * API Vimeo tak terjangkau — tampilan tetap fallback ke ikon placeholder).
     *
     * @throws \InvalidArgumentException bila URL bukan YouTube/Vimeo yang valid.
     */
    public function addVideo(Album $album, string $url, ?string $caption = null): AlbumItem
    {
        $parsed = VideoUrl::parse($url);

        if (! $parsed) {
            throw new \InvalidArgumentException('URL video tidak dikenali. Gunakan tautan YouTube atau Vimeo yang valid.');
        }

        $thumbnailUrl = $parsed['thumbnail_url'] ?? $this->fetchVimeoThumbnail($parsed['id']);

        $item = $album->items()->create([
            'video_url' => $url,
            'video_provider' => $parsed['provider'],
            'thumbnail_url' => $thumbnailUrl,
            'caption' => $caption,
            'sort_order' => $this->nextSortOrder($album),
        ]);

        $this->flushCache($album->slug);

        return $item;
    }

    public function removeItem(AlbumItem $item): void
    {
        $slug = $item->album->slug;
        $item->delete();
        $this->flushCache($slug);
    }

    private function nextSortOrder(Album $album): int
    {
        return ((int) $album->items()->max('sort_order')) + 1;
    }

    private function fetchVimeoThumbnail(string $videoId): ?string
    {
        try {
            $response = Http::timeout(5)->get('https://vimeo.com/api/oembed.json', [
                'url' => "https://vimeo.com/{$videoId}",
            ]);

            return $response->successful() ? $response->json('thumbnail_url') : null;
        } catch (\Throwable $e) {
            Log::warning("GalleryService: gagal mengambil thumbnail Vimeo untuk video {$videoId}: {$e->getMessage()}");

            return null;
        }
    }

    private function flushCache(string $slug): void
    {
        Cache::forget("public.album.{$slug}");
        Cache::forget('public.gallery.latest_items.6');
    }
}
