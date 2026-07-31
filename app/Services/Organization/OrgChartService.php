<?php

namespace App\Services\Organization;

use App\Models\OrgAssignment;
use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrgChartService
{
    public function activePeriod(): ?OrgPeriod
    {
        return OrgPeriod::where('is_active', true)->first();
    }

    /** Pengurus puncak (Ketua Umum) periode aktif — dipakai seksi Sambutan Ketua di beranda. */
    public function chairman(): ?OrgAssignment
    {
        $period = $this->activePeriod();

        if (! $period) {
            return null;
        }

        $rootUnit = OrgUnit::where('org_period_id', $period->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->first();

        return $rootUnit?->assignments()->with(['member', 'unit.period'])->orderBy('sort_order')->first();
    }

    public function tree(int $periodId): Collection
    {
        // Eager load 4 level; level lebih dalam tetap dirender komponen rekursif
        // via lazy-load (jarang terjadi, hanya sedikit query tambahan).
        return OrgUnit::where('org_period_id', $periodId)
            ->whereNull('parent_id')
            ->with(['children.children.children.assignments.member.media', 'assignments.member.media'])
            ->orderBy('sort_order')
            ->get();
    }

    public function activate(OrgPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            OrgPeriod::where('id', '!=', $period->id)->update(['is_active' => false]);
            $period->update(['is_active' => true]);
        });
    }

    public function copyStructureToNewPeriod(OrgPeriod $from, OrgPeriod $to): void
    {
        DB::transaction(function () use ($from, $to) {
            $rootUnits = OrgUnit::where('org_period_id', $from->id)->whereNull('parent_id')->orderBy('sort_order')->get();

            foreach ($rootUnits as $unit) {
                $this->copyUnitRecursively($unit, $to->id, null);
            }
        });
    }

    private function copyUnitRecursively(OrgUnit $unit, int $toPeriodId, ?int $newParentId): void
    {
        $copy = OrgUnit::create([
            'org_period_id' => $toPeriodId,
            'parent_id' => $newParentId,
            'name' => $unit->name,
            'sort_order' => $unit->sort_order,
        ]);

        foreach ($unit->children()->orderBy('sort_order')->get() as $child) {
            $this->copyUnitRecursively($child, $toPeriodId, $copy->id);
        }
    }
}
