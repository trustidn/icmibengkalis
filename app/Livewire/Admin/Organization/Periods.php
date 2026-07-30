<?php

namespace App\Livewire\Admin\Organization;

use App\Models\OrgPeriod;
use App\Services\Organization\OrgChartService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Periods extends Component
{
    public string $name = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public function mount(): void
    {
        $this->authorize('viewAny', OrgPeriod::class);
    }

    public function create(): void
    {
        $this->authorize('create', OrgPeriod::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        OrgPeriod::create($validated);

        $this->reset(['name', 'starts_at', 'ends_at']);
    }

    public function activate(int $periodId, OrgChartService $orgChart): void
    {
        $this->authorize('update', OrgPeriod::class);

        $orgChart->activate(OrgPeriod::findOrFail($periodId));
    }

    // Ubah nama/tahun periode kepengurusan (inline)
    public ?int $editingId = null;

    public string $editingName = '';

    public string $editingStartsAt = '';

    public string $editingEndsAt = '';

    public function startEdit(int $periodId): void
    {
        $this->authorize('update', OrgPeriod::class);

        $period = OrgPeriod::findOrFail($periodId);
        $this->editingId = $period->id;
        $this->editingName = $period->name;
        $this->editingStartsAt = $period->starts_at->format('Y-m-d');
        $this->editingEndsAt = $period->ends_at->format('Y-m-d');
    }

    public function saveEdit(): void
    {
        $this->authorize('update', OrgPeriod::class);

        $validated = $this->validate([
            'editingName' => ['required', 'string', 'max:255'],
            'editingStartsAt' => ['required', 'date'],
            'editingEndsAt' => ['required', 'date', 'after:editingStartsAt'],
        ]);

        OrgPeriod::findOrFail($this->editingId)->update([
            'name' => $validated['editingName'],
            'starts_at' => $validated['editingStartsAt'],
            'ends_at' => $validated['editingEndsAt'],
        ]);

        $this->reset(['editingId', 'editingName', 'editingStartsAt', 'editingEndsAt']);
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editingName', 'editingStartsAt', 'editingEndsAt']);
    }

    public function copyStructure(int $fromId, int $toId, OrgChartService $orgChart): void
    {
        $this->authorize('update', OrgPeriod::class);

        $orgChart->copyStructureToNewPeriod(OrgPeriod::findOrFail($fromId), OrgPeriod::findOrFail($toId));
    }

    public function render()
    {
        return view('livewire.admin.organization.periods', [
            'periods' => OrgPeriod::orderByDesc('starts_at')->get(),
        ]);
    }
}
