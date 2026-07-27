<?php

namespace App\Livewire\Public\Archive;

use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Show extends Component
{
    public Document $document;

    public function mount(string $slug): void
    {
        $document = Document::where('slug', $slug)->first();

        abort_unless($document, Response::HTTP_NOT_FOUND);
        abort_unless(Gate::forUser(auth()->user())->allows('view', $document), Response::HTTP_FORBIDDEN);

        $this->document = $document;
    }

    public function render()
    {
        return view('livewire.public.archive.show', ['document' => $this->document])
            ->layout('components.layouts.public', [
                'metaTitle' => $this->document->title.' — '.config('app.name'),
                'metaDescription' => Str::limit(strip_tags((string) $this->document->description), 160),
            ]);
    }
}
