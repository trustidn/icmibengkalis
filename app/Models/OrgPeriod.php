<?php

namespace App\Models;

use Database\Factories\OrgPeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgPeriod extends Model
{
    /** @use HasFactory<OrgPeriodFactory> */
    use HasFactory;

    protected $fillable = ['name', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function units(): HasMany
    {
        return $this->hasMany(OrgUnit::class);
    }

    public function rootUnits(): HasMany
    {
        return $this->hasMany(OrgUnit::class)->whereNull('parent_id')->orderBy('sort_order');
    }
}
