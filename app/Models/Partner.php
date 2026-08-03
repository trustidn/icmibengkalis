<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMediaConversions;
use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Link partner/mitra (ditampilkan sebagai strip logo di beranda).
 */
class Partner extends Model implements HasMedia
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory, InteractsWithMedia, ResolvesMediaConversions;

    protected $fillable = ['name', 'url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Logo jangan di-crop — cukup dibatasi dimensinya.
        $this->addMediaConversion('thumb')
            ->fit(Fit::Max, 600, 600)
            ->format('webp')
            ->quality(85);
    }

    public function logoUrl(): ?string
    {
        return $this->conversionUrl('logo', 'thumb');
    }

    /** Monogram fallback saat logo belum diupload — maksimal 2 huruf awal kata. */
    public function monogram(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
