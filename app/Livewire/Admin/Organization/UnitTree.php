<?php

namespace App\Livewire\Admin\Organization;

use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UnitTree extends Component
{
    public OrgPeriod $period;

    public string $name = '';

    public ?int $parent_id = null;

    public function mount(OrgPeriod $period): void
    {
        $this->authorize('viewAny', OrgUnit::class);
        $this->period = $period;
    }

    public function addUnit(): void
    {
        $this->authorize('create', OrgUnit::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:org_units,id'],
        ]);

        OrgUnit::create([
            ...$validated,
            'org_period_id' => $this->period->id,
        ]);

        $this->reset(['name', 'parent_id']);
    }

    public function deleteUnit(int $unitId): void
    {
        $this->authorize('delete', OrgUnit::class);

        OrgUnit::where('org_period_id', $this->period->id)->findOrFail($unitId)->delete();
    }

    public ?int $renamingId = null;

    public string $renamingName = '';

    public function startRename(int $unitId): void
    {
        $this->authorize('update', OrgUnit::class);

        $unit = OrgUnit::where('org_period_id', $this->period->id)->findOrFail($unitId);
        $this->renamingId = $unit->id;
        $this->renamingName = $unit->name;
    }

    public function saveRename(): void
    {
        $this->authorize('update', OrgUnit::class);

        $validated = $this->validate(['renamingName' => ['required', 'string', 'max:255']]);

        OrgUnit::where('org_period_id', $this->period->id)
            ->findOrFail($this->renamingId)
            ->update(['name' => $validated['renamingName']]);

        $this->reset(['renamingId', 'renamingName']);
    }

    public function cancelRename(): void
    {
        $this->reset(['renamingId', 'renamingName']);
    }

    public function render()
    {
        // Seluruh unit dimuat sekali lalu dikelompokkan per induk — view merender
        // pohon secara REKURSIF (kedalaman bebas), bukan hanya 2 level.
        $all = $this->period->units()->orderBy('sort_order')->orderBy('id')->get();

        return view('livewire.admin.organization.unit-tree', [
            'unitsByParent' => $all->groupBy(fn ($unit) => $unit->parent_id ?? 0),
            'allUnits' => $all->sortBy('name')->values(),
        ]);
    }
}
