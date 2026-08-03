<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMediaConversions;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Page extends Model implements HasMedia
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, InteractsWithMedia, ResolvesMediaConversions;

    protected $fillable = [
        'slug',
        'title',
        'body',
        'seo_meta',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'seo_meta' => 'array',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82);
    }

    public function featuredImageUrl(): ?string
    {
        return $this->conversionUrl('featured', 'large');
    }
}
