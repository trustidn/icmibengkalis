<?php

namespace App\Services\Archive;

use App\Jobs\ExtractDocumentText;
use App\Models\Document;
use App\Models\DocumentPermission;
use App\Models\DocumentVersion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class ArchiveService
{
    public function upload(array $data, int $uploadedBy, UploadedFile $file): Document
    {
        $document = Document::create([
            ...$data,
            'uploaded_by' => $uploadedBy,
            'current_version' => 1,
        ]);

        $this->storeVersion($document, $file, $uploadedBy, 1, null);

        return $document;
    }

    public function uploadNewVersion(Document $document, UploadedFile $file, int $uploadedBy, ?string $changeNote = null): DocumentVersion
    {
        $nextVersion = $document->current_version + 1;

        $version = $this->storeVersion($document, $file, $uploadedBy, $nextVersion, $changeNote);

        $document->update(['current_version' => $nextVersion]);

        return $version;
    }

    private function storeVersion(Document $document, UploadedFile $file, int $uploadedBy, int $versionNumber, ?string $changeNote): DocumentVersion
    {
        $version = $document->versions()->create([
            'version_number' => $versionNumber,
            'uploaded_by' => $uploadedBy,
            'change_note' => $changeNote,
            'file_hash' => hash_file('sha256', $file->getRealPath()),
        ]);

        // Disk "local" (config/filesystems.php) berakar di storage/app/private — file arsip tidak boleh publik.
        $version->addMedia($file)->toMediaCollection('versions', 'local');

        ExtractDocumentText::dispatch($version);

        return $version;
    }

    public function update(Document $document, array $data): Document
    {
        $document->update($data);

        return $document;
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $constrain = fn ($query) => $query
            ->with(['category', 'uploader'])
            ->when($filters['category_id'] ?? null, fn ($q, $value) => $q->where('document_category_id', $value))
            ->when($filters['doc_type'] ?? null, fn ($q, $value) => $q->where('doc_type', $value))
            ->when($filters['access_level'] ?? null, fn ($q, $value) => $q->where('access_level', $value));

        if ($search = $filters['search'] ?? null) {
            return Document::search($search)->query($constrain)->paginate($perPage);
        }

        return $constrain(Document::query())->latest()->paginate($perPage);
    }

    public function grantPermission(Document $document, string $granteeType, int $granteeId, string $ability = 'view'): DocumentPermission
    {
        return $document->permissions()->firstOrCreate([
            'grantee_type' => $granteeType,
            'grantee_id' => $granteeId,
            'ability' => $ability,
        ]);
    }

    public function revokePermission(DocumentPermission $permission): void
    {
        $permission->delete();
    }
}
