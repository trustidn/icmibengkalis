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

    public string $external_name = '';

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

        // Tepat satu identitas: anggota terdaftar ATAU nama tokoh eksternal
        // (mis. dewan penasehat dari luar organisasi). Bila anggota dipilih,
        // nama eksternal diabaikan.
        $validated = $this->validate([
            'member_id' => ['nullable', 'required_without:external_name', 'exists:members,id'],
            'external_name' => ['nullable', 'required_without:member_id', 'string', 'max:255'],
            'position_title' => ['required', 'string', 'max:255'],
            'short_bio' => ['nullable', 'string'],
            'show_contact' => ['boolean'],
        ], [
            'member_id.required_without' => 'Pilih anggota, atau isi nama tokoh eksternal.',
            'external_name.required_without' => 'Pilih anggota, atau isi nama tokoh eksternal.',
        ]);

        if ($validated['member_id']) {
            $validated['external_name'] = null;
        }

        $this->unit->assignments()->create($validated);

        $this->reset(['member_id', 'external_name', 'position_title', 'short_bio', 'show_contact']);
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
