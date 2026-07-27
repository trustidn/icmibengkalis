<?php

namespace App\Livewire\Admin\Professions;

use App\Models\Profession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $name = '';

    public ?int $editingId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Profession::class);
    }

    public function edit(int $id): void
    {
        $profession = Profession::findOrFail($id);
        $this->authorize('update', $profession);

        $this->editingId = $profession->id;
        $this->name = $profession->name;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:professions,name,'.$this->editingId],
        ]);

        if ($this->editingId) {
            $profession = Profession::findOrFail($this->editingId);
            $this->authorize('update', $profession);
            $profession->update($validated);
        } else {
            $this->authorize('create', Profession::class);
            Profession::create($validated);
        }

        $this->reset(['name', 'editingId']);
    }

    public function cancel(): void
    {
        $this->reset(['name', 'editingId']);
    }

    public function delete(int $id): void
    {
        $profession = Profession::findOrFail($id);
        $this->authorize('delete', $profession);

        $profession->delete();
    }

    public function render()
    {
        return view('livewire.admin.professions.index', [
            'professions' => Profession::orderBy('name')->paginate(20),
        ]);
    }
}
