<?php

namespace App\Livewire\Admin\Organization;

use App\Models\Member;
use App\Models\OrgAssignment;
use App\Models\OrgUnit;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AssignmentForm extends Component
{
    public OrgUnit $unit;

    public ?int $member_id = null;

    public string $position_title = '';

    public string $short_bio = '';

    public bool $show_contact = false;

    public function mount(OrgUnit $unit): void
    {
        $this->authorize('viewAny', OrgUnit::class);
        $this->unit = $unit;
    }

    public function addAssignment(): void
    {
        $this->authorize('update', OrgUnit::class);

        $validated = $this->validate([
            'member_id' => ['required', 'exists:members,id'],
            'position_title' => ['required', 'string', 'max:255'],
            'short_bio' => ['nullable', 'string'],
            'show_contact' => ['boolean'],
        ]);

        $this->unit->assignments()->create($validated);

        $this->reset(['member_id', 'position_title', 'short_bio', 'show_contact']);
    }

    public function deleteAssignment(int $assignmentId): void
    {
        $this->authorize('update', OrgUnit::class);

        OrgAssignment::where('org_unit_id', $this->unit->id)->findOrFail($assignmentId)->delete();
    }

    public function render()
    {
        return view('livewire.admin.organization.assignment-form', [
            'assignments' => $this->unit->assignments()->with('member')->get(),
            'members' => Member::orderBy('full_name')->get(),
        ]);
    }
}
