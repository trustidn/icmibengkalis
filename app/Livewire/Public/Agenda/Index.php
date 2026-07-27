<?php

namespace App\Livewire\Public\Agenda;

use App\Services\Agenda\AgendaService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render(AgendaService $agenda)
    {
        return view('livewire.public.agenda.index', [
            'events' => $agenda->upcoming(),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Agenda — '.config('app.name'),
        ]);
    }
}
