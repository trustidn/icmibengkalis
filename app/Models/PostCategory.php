<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    /** @use HasFactory<PostCategoryFactory> */
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'slug'];

    protected string $slugSourceField = 'name';

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
