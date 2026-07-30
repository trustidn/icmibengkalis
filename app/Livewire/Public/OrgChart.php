<?php

namespace App\Livewire\Public;

use App\Models\OrgPeriod;
use App\Services\Membership\MemberService;
use App\Services\Organization\OrgChartService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OrgChart extends Component
{
    use WithPagination;

    #[Url]
    public ?int $periodId = null;

    #[Url]
    public string $search = '';

    public function mount(OrgChartService $orgChart): void
    {
        $this->periodId = $this->periodId ?? $orgChart->activePeriod()?->id;
    }

    public function render(OrgChartService $orgChart, MemberService $members)
    {
        return view('livewire.public.org-chart', [
            'periods' => OrgPeriod::orderByDesc('starts_at')->get(),
            'units' => $this->periodId ? $orgChart->tree($this->periodId) : collect(),
            'members' => $members->paginateOrderedByPositionThenJoined($this->getPage(), perPage: 10),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Struktur Organisasi — '.config('app.name'),
        ]);
    }
}
