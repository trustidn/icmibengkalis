<?php

namespace App\Livewire\Admin\Agenda;

use App\Models\Event;
use App\Services\Agenda\AgendaService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', Event::class);
    }

    public function delete(int $eventId, AgendaService $agenda): void
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('delete', $event);

        $agenda->delete($event);
    }

    public function render(AgendaService $agenda)
    {
        return view('livewire.admin.agenda.index', [
            'events' => $agenda->paginateAll(),
        ]);
    }
}
