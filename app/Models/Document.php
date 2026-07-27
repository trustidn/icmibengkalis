<?php

namespace App\Models;

use App\Enums\DocType;
use App\Enums\DocumentAccessLevel;
use App\Models\Concerns\HasSlug;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, HasSlug, Searchable, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'document_number',
        'doc_type',
        'description',
        'document_category_id',
        'org_unit_id',
        'uploaded_by',
        'access_level',
        'document_date',
        'extracted_text',
        'current_version',
    ];

    protected function casts(): array
    {
        return [
            'doc_type' => DocType::class,
            'access_level' => DocumentAccessLevel::class,
            'document_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version_number');
    }

    public function latestVersion(): ?DocumentVersion
    {
        return $this->versions()->first();
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(DocumentPermission::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'extracted_text' => $this->extracted_text,
        ];
    }
}
