<?php

namespace App\Models;

use App\Enums\MemberStatus;
use App\Models\Concerns\HasSlug;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Member extends Model implements HasMedia
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory, HasSlug, InteractsWithMedia, SoftDeletes;

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

    public function photoUrl(): ?string
    {
        return $this->getFirstMediaUrl('photo') ?: null;
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
