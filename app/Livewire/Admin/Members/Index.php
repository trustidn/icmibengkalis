<?php

namespace App\Livewire\Admin\Members;

use App\Enums\EducationLevel;
use App\Models\District;
use App\Models\Member;
use App\Services\Membership\MemberService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $district_id = '';

    #[Url]
    public string $profession = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $education_level = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Member::class);
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function delete(int $memberId, MemberService $members): void
    {
        $this->authorize('delete', Member::class);

        $members->delete(Member::findOrFail($memberId));
    }

    public function render(MemberService $members)
    {
        return view('livewire.admin.members.index', [
            'members' => $members->paginate([
                'search' => $this->search,
                'district_id' => $this->district_id ?: null,
                'profession' => $this->profession ?: null,
                'status' => $this->status ?: null,
                'education_level' => $this->education_level ?: null,
            ]),
            'districts' => District::orderBy('name')->get(),
            'educationLevels' => EducationLevel::cases(),
        ]);
    }
}
