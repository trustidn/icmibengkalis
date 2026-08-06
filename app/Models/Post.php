<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\ResolvesMediaConversions;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasSlug, InteractsWithMedia, ResolvesMediaConversions, Searchable, SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'author_id',
        'post_category_id',
        'org_unit_id',
        'reviewed_by',
        'review_note',
        'published_at',
        'view_count',
        'seo_meta',
    ];

    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'seo_meta' => 'array',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class)->latest();
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === PostStatus::Published && $this->published_at?->lessThanOrEqualTo(now());
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 600)
            ->format('webp')
            ->quality(80);

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82);
    }

    /** Sampul artikel (detail & kartu besar) — konversi 'large'. */
    public function featuredImageUrl(): ?string
    {
        return $this->conversionUrl('featured', 'large');
    }

    /** Thumbnail kartu kecil/daftar — konversi 'thumb'. */
    public function featuredThumbUrl(): ?string
    {
        return $this->conversionUrl('featured', 'thumb');
    }
}
