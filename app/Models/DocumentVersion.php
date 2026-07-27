<?php

namespace App\Models;

use Database\Factories\DocumentVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DocumentVersion extends Model implements HasMedia
{
    /** @use HasFactory<DocumentVersionFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'document_id',
        'version_number',
        'uploaded_by',
        'change_note',
        'file_hash',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('versions')->singleFile();
    }

    public function getFileMedia()
    {
        return $this->getFirstMedia('versions');
    }
}
