<?php

namespace App\Livewire\Admin\Archive;

use App\Enums\DocType;
use App\Enums\DocumentAbility;
use App\Enums\DocumentAccessLevel;
use App\Enums\DocumentGranteeType;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Services\Archive\ArchiveService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?Document $document = null;

    public string $title = '';

    public string $document_number = '';

    public string $doc_type = 'lainnya';

    public string $description = '';

    public ?int $document_category_id = null;

    public string $access_level = 'anggota';

    public string $document_date = '';

    public $file = null;

    public string $changeNote = '';

    public string $granteeType = 'user';

    public string $granteeId = '';

    public function mount(?Document $document = null): void
    {
        if ($document?->exists) {
            $this->authorize('update', Document::class);
            $this->document = $document;
            $this->title = $document->title;
            $this->document_number = (string) $document->document_number;
            $this->doc_type = $document->doc_type->value;
            $this->description = (string) $document->description;
            $this->document_category_id = $document->document_category_id;
            $this->access_level = $document->access_level->value;
            $this->document_date = $document->document_date?->format('Y-m-d') ?? '';
        } else {
            $this->authorize('create', Document::class);
        }
    }

    public function save(ArchiveService $archive): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:255'],
            'doc_type' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, DocType::cases()))],
            'description' => ['nullable', 'string'],
            'document_category_id' => ['nullable', 'exists:document_categories,id'],
            'access_level' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, DocumentAccessLevel::cases()))],
            'document_date' => ['nullable', 'date'],
        ]);

        // Tanggal kosong wajib null — MariaDB strict menolak '' untuk kolom DATE.
        $validated['document_date'] = $validated['document_date'] ?: null;

        if ($this->document) {
            $archive->update($this->document, $validated);
            $this->redirectRoute('admin.archive.index', navigate: true);

            return;
        }

        $this->validate(['file' => ['required', 'file', 'max:20480']]);

        $this->document = $archive->upload($validated, auth()->id(), $this->file);

        $this->redirectRoute('admin.archive.index', navigate: true);
    }

    public function uploadVersion(ArchiveService $archive): void
    {
        $this->authorize('update', Document::class);

        $this->validate([
            'file' => ['required', 'file', 'max:20480'],
            'changeNote' => ['nullable', 'string', 'max:500'],
        ]);

        $archive->uploadNewVersion($this->document, $this->file, auth()->id(), $this->changeNote ?: null);

        $this->reset(['file', 'changeNote']);
        $this->document->refresh();
    }

    public function grantPermission(ArchiveService $archive): void
    {
        $this->authorize('manageAccess', Document::class);

        $validated = $this->validate([
            'granteeType' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, DocumentGranteeType::cases()))],
            'granteeId' => ['required', 'integer'],
        ]);

        $archive->grantPermission($this->document, $validated['granteeType'], (int) $validated['granteeId'], DocumentAbility::View->value);

        $this->reset(['granteeId']);
    }

    public function revokePermission(int $permissionId, ArchiveService $archive): void
    {
        $this->authorize('manageAccess', Document::class);

        $archive->revokePermission($this->document->permissions()->findOrFail($permissionId));
    }

    public function render()
    {
        return view('livewire.admin.archive.form', [
            'docTypes' => DocType::cases(),
            'accessLevels' => DocumentAccessLevel::cases(),
            'categories' => DocumentCategory::orderBy('name')->get(),
            'granteeTypes' => DocumentGranteeType::cases(),
            'users' => User::orderBy('name')->get(),
            'versions' => $this->document?->versions()->with('uploader')->get() ?? collect(),
            'permissions' => $this->document?->permissions()->get() ?? collect(),
        ]);
    }
}
