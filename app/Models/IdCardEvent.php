<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMediaConversions;
use Database\Factories\IdCardEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IdCardEvent extends Model implements HasMedia
{
    /** @use HasFactory<IdCardEventFactory> */
    use HasFactory, InteractsWithMedia, ResolvesMediaConversions;

    protected $fillable = [
        'name',
        'event_date',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        // Desain latar kartu (potret 54 x 85,6 mm) — diunggah admin per kegiatan.
        $this->addMediaCollection('background')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1200, 1900)
            ->format('webp')
            ->quality(85);
    }

    public function backgroundUrl(): ?string
    {
        return $this->conversionUrl('background', 'large');
    }

    /** Path file latar untuk PDF (dompdf butuh berkas lokal, bukan URL). */
    public function backgroundPath(): ?string
    {
        $media = $this->getFirstMedia('background');

        return $media?->getPath();
    }
}
