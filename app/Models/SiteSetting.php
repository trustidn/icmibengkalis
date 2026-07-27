<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Konfigurasi web (singleton satu baris): identitas situs, kontak, logo, dan gambar hero.
 */
class SiteSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'site_name',
        'tagline',
        'address',
        'email',
        'phone',
        'facebook',
        'instagram',
        'youtube',
        'registration_enabled',
    ];

    protected function casts(): array
    {
        return [
            'registration_enabled' => 'boolean',
        ];
    }

    private static ?self $current = null;

    public static function current(): self
    {
        return self::$current ??= self::query()->with('media')->firstOrCreate([], [
            'site_name' => __('nav.site_name'),
            'registration_enabled' => true,
        ]);
    }

    /** Reset memoization per-request setelah admin mengubah konfigurasi. */
    public static function forgetCurrent(): void
    {
        self::$current = null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('hero')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
    }

    public function logoUrl(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: null;
    }

    public function heroUrl(): ?string
    {
        return $this->getFirstMediaUrl('hero') ?: null;
    }

    public function faviconUrl(): ?string
    {
        return $this->getFirstMediaUrl('favicon') ?: null;
    }
}
