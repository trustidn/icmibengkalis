<?php

namespace App\Livewire\Admin\IdCard;

use App\Enums\MemberStatus;
use App\Models\IdCardEvent;
use App\Models\Member;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public ?string $event_date = null;

    public $background = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('idcard.manage'), 403);
    }

    public function create(): void
    {
        $this->reset(['name', 'event_date', 'background', 'editingId']);
        $this->showForm = true;
    }

    public function edit(int $eventId): void
    {
        $event = IdCardEvent::findOrFail($eventId);

        $this->editingId = $event->id;
        $this->name = $event->name;
        $this->event_date = $event->event_date?->format('Y-m-d');
        $this->background = null;
        $this->showForm = true;
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'event_date', 'background', 'editingId']);
        $this->showForm = false;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'background' => [$this->editingId ? 'nullable' : 'required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ], [
            'background.required' => 'Unggah desain latar kartu (potret, rasio 54:85,6 — mis. 1080x1712 px).',
        ]);

        if (blank($validated['event_date'] ?? null)) {
            unset($validated['event_date']);
        }

        if ($this->editingId) {
            $event = IdCardEvent::findOrFail($this->editingId);
            $event->update(['name' => $validated['name'], 'event_date' => $validated['event_date'] ?? $event->event_date]);
        } else {
            $event = IdCardEvent::create([
                'name' => $validated['name'],
                'event_date' => $validated['event_date'] ?? null,
                'created_by' => auth()->id(),
            ]);
        }

        if ($this->background) {
            $event->addMedia($this->background->getRealPath())
                ->usingFileName('background.'.$this->background->getClientOriginalExtension())
                ->toMediaCollection('background');
        }

        $this->cancelEdit();
    }

    public function toggleActive(int $eventId): void
    {
        $event = IdCardEvent::findOrFail($eventId);
        $event->update(['is_active' => ! $event->is_active]);
    }

    public function delete(int $eventId): void
    {
        IdCardEvent::findOrFail($eventId)->delete();
    }

    public function render()
    {
        return view('livewire.admin.id-card.index', [
            'events' => IdCardEvent::latest()->paginate(15),
            'memberCount' => Member::where('status', MemberStatus::Aktif)->count(),
        ]);
    }
}
