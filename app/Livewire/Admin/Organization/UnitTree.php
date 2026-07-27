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

    public function render()
    {
        return view('livewire.admin.organization.unit-tree', [
            'units' => $this->period->units()->with('children')->whereNull('parent_id')->orderBy('sort_order')->get(),
            'allUnits' => $this->period->units()->orderBy('name')->get(),
        ]);
    }
}
