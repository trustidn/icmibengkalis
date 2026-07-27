<?php

namespace App\Livewire\Admin\Archive;

use App\Models\DocumentCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Categories extends Component
{
    public string $name = '';

    public ?int $parent_id = null;

    public ?int $editingId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', DocumentCategory::class);
    }

    public function edit(int $id): void
    {
        $category = DocumentCategory::findOrFail($id);
        $this->authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->parent_id = $category->parent_id;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:document_categories,id', 'different:editingId'],
        ]);

        if ($this->editingId) {
            $category = DocumentCategory::findOrFail($this->editingId);
            $this->authorize('update', $category);
            $category->update($validated);
        } else {
            $this->authorize('create', DocumentCategory::class);
            DocumentCategory::create($validated);
        }

        $this->reset(['name', 'parent_id', 'editingId']);
    }

    public function cancel(): void
    {
        $this->reset(['name', 'parent_id', 'editingId']);
    }

    public function delete(int $id): void
    {
        $category = DocumentCategory::findOrFail($id);
        $this->authorize('delete', $category);

        $category->delete();
    }

    public function render()
    {
        return view('livewire.admin.archive.categories', [
            'roots' => DocumentCategory::whereNull('parent_id')->with('children')->orderBy('name')->get(),
            'parents' => DocumentCategory::orderBy('name')->get(),
        ]);
    }
}
