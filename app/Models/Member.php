<?php

namespace App\Models;

use App\Enums\MemberStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\ResolvesMediaConversions;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Member extends Model implements HasMedia
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory, HasSlug, InteractsWithMedia, ResolvesMediaConversions, SoftDeletes;

    protected string $slugSourceField = 'full_name';

    protected $fillable = [
        'user_id',
        'nia',
        'slug',
        'full_name',
        'title_prefix',
        'title_suffix',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'district_id',
        'institution',
        'profession_id',
        'profession',
        'expertise',
        'bio',
        'social_links',
        'status',
        'joined_at',
        'show_contact_public',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'joined_at' => 'date',
            'social_links' => 'array',
            'status' => MemberStatus::class,
            'show_contact_public' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Cukup satu ukuran: avatar chip, kartu anggota, dan foto profil publik.
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 600)
            ->format('webp')
            ->quality(80);
    }

    public function photoUrl(): ?string
    {
        return $this->conversionUrl('photo', 'thumb');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(MemberEducation::class);
    }

    public function expertises(): HasMany
    {
        return $this->hasMany(MemberExpertise::class);
    }

    /** Nama lengkap beserta gelar depan & belakang, mis. "Dr. H. Fulan, M.Pd." */
    public function fullNameWithTitles(): string
    {
        $nama = trim(collect([$this->title_prefix, $this->full_name])->filter()->implode(' '));

        return filled($this->title_suffix)
            ? $nama.', '.ltrim(trim($this->title_suffix), ', ')
            : $nama;
    }

    public function links(): HasMany
    {
        return $this->hasMany(MemberLink::class)->orderBy('sort_order')->orderBy('id');
    }

    public function orgAssignments(): HasMany
    {
        return $this->hasMany(OrgAssignment::class);
    }

    public function isPengurus(): bool
    {
        return $this->orgAssignments()
            ->whereHas('unit.period', fn ($query) => $query->where('is_active', true))
            ->exists();
    }
}
