<?php

namespace App\Livewire\Admin\Agenda;

use App\Models\Event;
use App\Services\Agenda\AgendaService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Event $event = null;

    public string $title = '';

    public string $description = '';

    public string $location = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public bool $is_published = false;

    public function mount(?Event $event = null): void
    {
        if ($event?->exists) {
            $this->authorize('update', $event);
            $this->event = $event;
            $this->title = $event->title;
            $this->description = (string) $event->description;
            $this->location = (string) $event->location;
            $this->starts_at = $event->starts_at->format('Y-m-d\TH:i');
            $this->ends_at = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
            $this->is_published = $event->is_published;
        } else {
            $this->authorize('create', Event::class);
        }
    }

    public function save(AgendaService $agenda): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_published' => ['boolean'],
        ]);

        if ($this->event) {
            $this->authorize('update', $this->event);
            $agenda->update($this->event, $validated);
        } else {
            $this->event = $agenda->create([...$validated, 'created_by' => auth()->id()]);
        }

        $this->redirectRoute('admin.agenda.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.agenda.form');
    }
}
