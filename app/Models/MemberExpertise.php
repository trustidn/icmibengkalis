<?php

namespace App\Models;

use App\Enums\ExpertiseClaimStatus;
use App\Enums\ExpertiseLevel;
use Database\Factories\MemberExpertiseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberExpertise extends Model
{
    /** @use HasFactory<MemberExpertiseFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id',
        'expertise_field_id',
        'level',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'level' => ExpertiseLevel::class,
            'status' => ExpertiseClaimStatus::class,
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function expertiseField(): BelongsTo
    {
        return $this->belongsTo(ExpertiseField::class);
    }
}
