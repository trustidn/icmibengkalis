<?php

namespace App\Models;

use App\Enums\EducationLevel;
use Database\Factories\MemberEducationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberEducation extends Model
{
    /** @use HasFactory<MemberEducationFactory> */
    use HasFactory;

    protected $table = 'member_educations';

    protected $fillable = [
        'member_id',
        'level',
        'institution',
        'major',
        'graduated_year',
    ];

    protected function casts(): array
    {
        return [
            'level' => EducationLevel::class,
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
