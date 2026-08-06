<?php

namespace App\Livewire\Public;

use App\Models\OrgPeriod;
use App\Services\Membership\MemberService;
use App\Services\Organization\OrgChartService;
use Livewire\Attributes\Url;
use Livewire\Component;

class OrgChart extends Component
{
    /** Jumlah profil per "halaman" load-more. */
    private const PER_BATCH = 12;

    #[Url]
    public ?int $periodId = null;

    #[Url]
    public string $search = '';

    public int $limit = self::PER_BATCH;

    public function mount(OrgChartService $orgChart): void
    {
        $this->periodId = $this->periodId ?? $orgChart->activePeriod()?->id;
    }

    /** Muat 12 profil berikutnya tanpa memuat ulang halaman. */
    public function loadMore(): void
    {
        $this->limit += self::PER_BATCH;
    }

    public function render(OrgChartService $orgChart, MemberService $members)
    {
        $ordered = $members->orderedForOrgChart();

        return view('livewire.public.org-chart', [
            'periods' => OrgPeriod::orderByDesc('starts_at')->get(),
            'units' => $this->periodId ? $orgChart->tree($this->periodId) : collect(),
            'members' => $ordered->take($this->limit),
            'hasMore' => $ordered->count() > $this->limit,
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Struktur Organisasi — '.config('app.name'),
        ]);
    }
}
