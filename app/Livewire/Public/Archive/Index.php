<?php

namespace App\Livewire\Public\Archive;

use App\Enums\DocType;
use App\Enums\DocumentAccessLevel;
use App\Models\Document;
use App\Models\DocumentCategory;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category_id = '';

    #[Url]
    public string $doc_type = '';

    private function visibleLevels(): array
    {
        // Dua jenis arsip utama (publik & khusus anggota) SELALU tampil di daftar
        // untuk semua pengunjung — dokumen anggota digambarkan terkunci bagi yang
        // tidak berhak; AKSES sesungguhnya tetap dijaga DocumentPolicy di halaman
        // detail dan unduhan. Level pengurus hanya terlihat oleh pengurus.
        $levels = [DocumentAccessLevel::Publik->value, DocumentAccessLevel::Anggota->value];

        if (auth()->user()?->member?->isPengurus()) {
            $levels[] = DocumentAccessLevel::Pengurus->value;
        }

        return $levels;
    }

    public function render()
    {
        $documents = Document::query()
            ->with(['category'])
            ->whereIn('access_level', $this->visibleLevels())
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->category_id, fn ($q) => $q->where('document_category_id', $this->category_id))
            ->when($this->doc_type, fn ($q) => $q->where('doc_type', $this->doc_type))
            ->latest()
            ->paginate(12);

        return view('livewire.public.archive.index', [
            'documents' => $documents,
            'categories' => DocumentCategory::orderBy('name')->get(),
            'docTypes' => DocType::cases(),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Arsip Digital — '.config('app.name'),
        ]);
    }
}
