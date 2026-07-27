<?php

namespace App\Models;

use App\Enums\DocumentAbility;
use App\Enums\DocumentGranteeType;
use Database\Factories\DocumentPermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPermission extends Model
{
    /** @use HasFactory<DocumentPermissionFactory> */
    use HasFactory;

    protected $fillable = ['document_id', 'grantee_type', 'grantee_id', 'ability'];

    protected function casts(): array
    {
        return [
            'grantee_type' => DocumentGranteeType::class,
            'ability' => DocumentAbility::class,
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
