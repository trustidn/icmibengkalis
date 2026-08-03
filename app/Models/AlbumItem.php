<?php

namespace App\Models;

use App\Support\VideoUrl;
use Database\Factories\AlbumItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AlbumItem extends Model implements HasMedia
{
    /** @use HasFactory<AlbumItemFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'album_id',
        'video_url',
        'video_provider',
        'thumbnail_url',
        'caption',
        'sort_order',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Grid/kartu memuat 'thumb', lightbox memuat 'large' — file asli tetap
        // tersimpan tapi tidak pernah dikirim ke pengunjung.
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 600)
            ->format('webp')
            ->quality(80);

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82);
    }

    public function isVideo(): bool
    {
        return filled($this->video_url);
    }

    /** Thumbnail item — konversi 'thumb' foto, atau thumbnail video (YouTube pola tetap, Vimeo hasil oEmbed). */
    public function thumbnailUrl(): ?string
    {
        if ($this->isVideo()) {
            return $this->thumbnail_url;
        }

        return $this->photoConversionUrl('thumb');
    }

    /** Versi besar untuk lightbox — konversi 'large' foto. */
    public function largeUrl(): ?string
    {
        if ($this->isVideo()) {
            return null;
        }

        return $this->photoConversionUrl('large');
    }

    /** URL konversi foto; jatuh ke file asli selama konversi belum/tidak tersedia. */
    private function photoConversionUrl(string $conversion): ?string
    {
        $media = $this->getFirstMedia('photo');

        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();
    }

    public function embedUrl(): ?string
    {
        if (! $this->isVideo()) {
            return null;
        }

        return VideoUrl::parse($this->video_url)['embed_url'] ?? null;
    }
}
