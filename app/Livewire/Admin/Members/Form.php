<?php

namespace App\Livewire\Admin\Members;

use App\Enums\EducationLevel;
use App\Enums\MemberStatus;
use App\Models\District;
use App\Models\Member;
use App\Models\MemberEducation;
use App\Services\Membership\MemberService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?Member $member = null;

    public $photo = null;

    public string $bio = '';

    public string $website = '';

    public string $whatsapp = '';

    public string $linkedin = '';

    public string $eduLevel = 'S1';

    public string $eduInstitution = '';

    public string $eduMajor = '';

    public string $eduGraduatedYear = '';

    public string $full_name = '';

    public string $title_prefix = '';

    public string $title_suffix = '';

    public string $gender = '';

    public string $birth_place = '';

    public string $birth_date = '';

    public string $address = '';

    public ?int $district_id = null;

    public string $institution = '';

    public string $profession = '';

    public string $expertise = '';

    public string $status = 'aktif';

    public string $joined_at = '';

    public bool $show_contact_public = false;

    public function mount(?Member $member = null): void
    {
        if ($member?->exists) {
            $this->authorize('update', $member);
            $this->member = $member;
            $this->full_name = $member->full_name;
            $this->title_prefix = (string) $member->title_prefix;
            $this->title_suffix = (string) $member->title_suffix;
            $this->gender = (string) $member->gender;
            $this->birth_place = (string) $member->birth_place;
            $this->birth_date = $member->birth_date?->format('Y-m-d') ?? '';
            $this->address = (string) $member->address;
            $this->district_id = $member->district_id;
            $this->institution = (string) $member->institution;
            $this->profession = (string) $member->profession;
            $this->expertise = (string) $member->expertise;
            $this->status = $member->status->value;
            $this->joined_at = $member->joined_at?->format('Y-m-d') ?? '';
            $this->show_contact_public = $member->show_contact_public;
            $this->bio = (string) $member->bio;
            $this->website = (string) ($member->social_links['website'] ?? '');
            $this->whatsapp = (string) ($member->social_links['whatsapp'] ?? '');
            $this->linkedin = (string) ($member->social_links['linkedin'] ?? '');
        } else {
            $this->authorize('create', Member::class);
            $this->joined_at = now()->format('Y-m-d');
        }
    }

    public function save(MemberService $members): void
    {
        $validated = $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'title_prefix' => ['nullable', 'string', 'max:50'],
            'title_suffix' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'institution' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'expertise' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, MemberStatus::cases()))],
            'joined_at' => ['nullable', 'date'],
            'show_contact_public' => ['boolean'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        // Field opsional kosong WAJIB null, bukan string kosong — MariaDB strict mode
        // menolak '' untuk kolom DATE (birth_date/joined_at) -> error 500 saat simpan.
        foreach (['title_prefix', 'title_suffix', 'gender', 'birth_place', 'birth_date', 'address', 'institution', 'profession', 'expertise', 'joined_at', 'bio'] as $field) {
            $validated[$field] = $validated[$field] ?: null;
        }

        $data = collect($validated)->except(['website', 'whatsapp', 'linkedin', 'photo'])->all();
        $data['social_links'] = array_filter([
            'website' => $validated['website'] ?: null,
            'whatsapp' => $validated['whatsapp'] ?: null,
            'linkedin' => $validated['linkedin'] ?: null,
        ]);

        if ($this->member) {
            $members->update($this->member, $data);
        } else {
            $this->member = $members->create($data);
        }

        if ($this->photo) {
            $this->member->addMedia($this->photo->getRealPath())
                ->usingFileName('photo.'.$this->photo->getClientOriginalExtension())
                ->toMediaCollection('photo');
        }

        $this->redirectRoute('admin.members.index', navigate: true);
    }

    public function removePhoto(): void
    {
        $this->authorize('update', $this->member);

        $this->member->clearMediaCollection('photo');
    }

    public function addEducation(): void
    {
        $this->authorize('update', $this->member);

        $validated = $this->validate([
            'eduLevel' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, EducationLevel::cases()))],
            'eduInstitution' => ['required', 'string', 'max:255'],
            'eduMajor' => ['nullable', 'string', 'max:255'],
            'eduGraduatedYear' => ['nullable', 'integer', 'min:1950', 'max:'.now()->year],
        ]);

        $this->member->educations()->create([
            'level' => $validated['eduLevel'],
            'institution' => $validated['eduInstitution'],
            'major' => $validated['eduMajor'] ?: null,
            'graduated_year' => $validated['eduGraduatedYear'] ?: null,
        ]);

        $this->reset(['eduLevel', 'eduInstitution', 'eduMajor', 'eduGraduatedYear']);
        $this->eduLevel = 'S1';
    }

    public function deleteEducation(int $id): void
    {
        $this->authorize('update', $this->member);

        MemberEducation::where('member_id', $this->member->id)->findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.members.form', [
            'districts' => District::orderBy('name')->get(),
            'statuses' => MemberStatus::cases(),
            'educationLevels' => EducationLevel::cases(),
            'educations' => $this->member?->educations()->get() ?? collect(),
        ]);
    }
}
