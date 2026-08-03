<?php

namespace App\Livewire\Public\Archive;

use App\Enums\DocType;
use App\Enums\DocumentAccessLevel;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\Archive\ArchiveService;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Unggah dokumen arsip oleh user login (bukan halaman admin).
 * Level akses yang bisa dipilih dibatasi publik/anggota — level pengurus/terbatas
 * hanya lewat halaman admin oleh pengelola arsip.
 */
class Upload extends Component
{
    use WithFileUploads;

    public string $title = '';

    public string $doc_type = '';

    public string $document_category_id = '';

    public string $description = '';

    public string $access_level = DocumentAccessLevel::Anggota->value;

    public $file = null;

    public function mount(): void
    {
        $this->authorize('create', Document::class);
    }

    public function save(ArchiveService $archive): void
    {
        $this->authorize('create', Document::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'doc_type' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, DocType::cases()))],
            'document_category_id' => ['nullable', 'exists:document_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'access_level' => ['required', 'in:'.DocumentAccessLevel::Publik->value.','.DocumentAccessLevel::Anggota->value],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $document = $archive->upload([
            'title' => $validated['title'],
            'doc_type' => $validated['doc_type'],
            'document_category_id' => $validated['document_category_id'] ?: null,
            'description' => $validated['description'] ?: null,
            'access_level' => $validated['access_level'],
        ], auth()->id(), $this->file);

        $this->redirectRoute('archive.show', $document->slug, navigate: true);
    }

    public function render()
    {
        return view('livewire.public.archive.upload', [
            'docTypes' => DocType::cases(),
            'categories' => DocumentCategory::orderBy('name')->get(),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Unggah Dokumen — '.config('app.name'),
        ]);
    }
}
