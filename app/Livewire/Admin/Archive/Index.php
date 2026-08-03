<?php

namespace App\Livewire\Admin\Archive;

use App\Enums\DocType;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\Archive\ArchiveService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category_id = '';

    #[Url]
    public string $doc_type = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Document::class);
    }

    public function delete(int $documentId, ArchiveService $archive): void
    {
        $document = Document::findOrFail($documentId);
        $this->authorize('delete', $document);

        $archive->delete($document);
    }

    public function render(ArchiveService $archive)
    {
        return view('livewire.admin.archive.index', [
            'documents' => $archive->paginate([
                'search' => $this->search,
                'category_id' => $this->category_id ?: null,
                'doc_type' => $this->doc_type ?: null,
            ]),
            'categories' => DocumentCategory::orderBy('name')->get(),
            'docTypes' => DocType::cases(),
        ]);
    }
}
