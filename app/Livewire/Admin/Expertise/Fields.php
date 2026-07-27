<?php

namespace App\Livewire\Admin\Expertise;

use App\Models\ExpertiseField;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Fields extends Component
{
    public string $name = '';

    public ?int $parent_id = null;

    public ?int $editingId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', ExpertiseField::class);
    }

    public function edit(int $id): void
    {
        $field = ExpertiseField::findOrFail($id);
        $this->authorize('update', $field);

        $this->editingId = $field->id;
        $this->name = $field->name;
        $this->parent_id = $field->parent_id;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:expertise_fields,id', 'different:editingId'],
        ]);

        if ($this->editingId) {
            $field = ExpertiseField::findOrFail($this->editingId);
            $this->authorize('update', $field);
            $field->update($validated);
        } else {
            $this->authorize('create', ExpertiseField::class);
            ExpertiseField::create($validated);
        }

        $this->reset(['name', 'parent_id', 'editingId']);
    }

    public function cancel(): void
    {
        $this->reset(['name', 'parent_id', 'editingId']);
    }

    public function delete(int $id): void
    {
        $field = ExpertiseField::findOrFail($id);
        $this->authorize('delete', $field);

        $field->delete();
    }

    public function render()
    {
        return view('livewire.admin.expertise.fields', [
            'roots' => ExpertiseField::whereNull('parent_id')->with('children')->orderBy('name')->get(),
            'parents' => ExpertiseField::orderBy('name')->get(),
        ]);
    }
}
