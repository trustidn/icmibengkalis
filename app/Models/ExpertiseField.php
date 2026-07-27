<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\ExpertiseFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpertiseField extends Model
{
    /** @use HasFactory<ExpertiseFieldFactory> */
    use HasFactory, HasSlug;

    protected $fillable = ['parent_id', 'name', 'slug', 'description'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }
}
