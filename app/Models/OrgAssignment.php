<?php

namespace App\Models;

use Database\Factories\OrgAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgAssignment extends Model
{
    /** @use HasFactory<OrgAssignmentFactory> */
    use HasFactory;

    protected $fillable = ['org_unit_id', 'member_id', 'position_title', 'sort_order', 'short_bio', 'show_contact'];

    protected function casts(): array
    {
        return [
            'show_contact' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'org_unit_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
