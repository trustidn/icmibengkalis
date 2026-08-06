<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostLike extends Model
{
    protected $fillable = [
        'post_id',
        'liker_key',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
