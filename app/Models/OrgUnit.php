<?php

namespace App\Models;

use Database\Factories\OrgUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgUnit extends Model
{
    /** @use HasFactory<OrgUnitFactory> */
    use HasFactory;

    protected $fillable = ['org_period_id', 'parent_id', 'name', 'sort_order'];

    public function period(): BelongsTo
    {
        return $this->belongsTo(OrgPeriod::class, 'org_period_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OrgAssignment::class)->orderBy('sort_order');
    }
}
