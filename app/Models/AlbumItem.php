<?php

namespace App\Models;

use App\Support\VideoUrl;
use Database\Factories\AlbumItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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

    public function isVideo(): bool
    {
        return filled($this->video_url);
    }

    /** Thumbnail item — foto asli/eksternal, atau thumbnail video (YouTube pola tetap, Vimeo hasil oEmbed). */
    public function thumbnailUrl(): ?string
    {
        if ($this->isVideo()) {
            return $this->thumbnail_url;
        }

        return $this->getFirstMediaUrl('photo') ?: null;
    }

    public function embedUrl(): ?string
    {
        if (! $this->isVideo()) {
            return null;
        }

        return VideoUrl::parse($this->video_url)['embed_url'] ?? null;
    }
}
