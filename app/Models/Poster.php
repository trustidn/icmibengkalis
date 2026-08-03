<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMediaConversions;
use Database\Factories\PosterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Poster ucapan beranda (hari jadi, hari kemerdekaan, dll.).
 */
class Poster extends Model implements HasMedia
{
    /** @use HasFactory<PosterFactory> */
    use HasFactory, InteractsWithMedia, ResolvesMediaConversions;

    protected $fillable = ['title', 'link_url', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82);
    }

    public function imageUrl(): ?string
    {
        return $this->conversionUrl('image', 'large');
    }

    /** Aktif DAN berada dalam masa tayang (batas tanggal opsional). */
    public function scopeCurrentlyVisible(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }
}
