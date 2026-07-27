<?php

namespace App\Livewire\Public\Agenda;

use App\Services\Agenda\AgendaService;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Show extends Component
{
    public string $slug;

    public function mount(string $slug, AgendaService $agenda): void
    {
        $event = $agenda->findBySlug($slug);

        abort_unless($event, Response::HTTP_NOT_FOUND);

        $this->slug = $slug;
    }

    public function render(AgendaService $agenda)
    {
        $event = $agenda->findBySlug($this->slug);

        return view('livewire.public.agenda.show', ['event' => $event])
            ->layout('components.layouts.public', [
                'metaTitle' => $event->title.' — '.config('app.name'),
                'metaDescription' => Str::limit(strip_tags((string) $event->description), 160),
            ]);
    }
}
