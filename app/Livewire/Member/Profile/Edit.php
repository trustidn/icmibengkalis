<?php

namespace App\Livewire\Member\Profile;

use App\Enums\EducationLevel;
use App\Models\District;
use App\Models\Member;
use App\Models\MemberEducation;
use App\Models\MemberLink;
use App\Services\Membership\MemberService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    use WithFileUploads;

    public Member $member;

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

    public string $bio = '';

    public bool $show_contact_public = false;

    // Tautan & media sosial (bisa lebih dari satu per jenis)
    public string $linkType = 'website';

    public string $linkLabel = '';

    public string $linkValue = '';

    public $photo = null;

    public bool $saved = false;

    // Riwayat pendidikan (paritas dengan form admin anggota)
    public string $eduLevel = 'S1';

    public string $eduInstitution = '';

    public string $eduMajor = '';

    public string $eduGraduatedYear = '';

    public function mount(): void
    {
        $member = auth()->user()->member;

        abort_unless($member, 404, 'Akun Anda belum tertaut ke data anggota. Hubungi pengurus.');

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
        $this->bio = (string) $member->bio;
        $this->show_contact_public = $member->show_contact_public;
    }

    public function save(MemberService $members): void
    {
        $this->authorize('update', $this->member);

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
            'bio' => ['nullable', 'string', 'max:2000'],
            'show_contact_public' => ['boolean'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        // Field opsional kosong WAJIB null, bukan string kosong — MariaDB strict mode
        // menolak '' untuk kolom DATE (birth_date) sehingga simpan profil error 500.
        $members->update($this->member, [
            'full_name' => $validated['full_name'],
            'title_prefix' => $validated['title_prefix'] ?: null,
            'title_suffix' => $validated['title_suffix'] ?: null,
            'gender' => $validated['gender'] ?: null,
            'birth_place' => $validated['birth_place'] ?: null,
            'birth_date' => $validated['birth_date'] ?: null,
            'address' => $validated['address'] ?: null,
            'district_id' => $validated['district_id'],
            'institution' => $validated['institution'] ?: null,
            'profession' => $validated['profession'] ?: null,
            'expertise' => $validated['expertise'] ?: null,
            'bio' => $validated['bio'] ?: null,
            'show_contact_public' => $validated['show_contact_public'],
        ]);

        if ($this->photo) {
            $this->member->addMedia($this->photo->getRealPath())
                ->usingFileName('photo.'.$this->photo->getClientOriginalExtension())
                ->toMediaCollection('photo');
            $this->photo = null;
        }

        $this->saved = true;
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

    public function addLink(): void
    {
        $this->authorize('update', $this->member);

        $validated = $this->validate([
            'linkType' => ['required', 'in:'.implode(',', array_keys(MemberLink::TYPES))],
            'linkLabel' => ['nullable', 'string', 'max:50'],
            'linkValue' => ['required', 'string', 'max:255'],
        ], [], ['linkValue' => 'alamat/nomor', 'linkLabel' => 'label']);

        $this->member->links()->create([
            'type' => $validated['linkType'],
            'label' => $validated['linkLabel'] ?: null,
            'value' => trim($validated['linkValue']),
            'sort_order' => ((int) $this->member->links()->max('sort_order')) + 1,
        ]);

        $this->reset(['linkLabel', 'linkValue']);
    }

    public function deleteLink(int $linkId): void
    {
        $this->authorize('update', $this->member);

        $this->member->links()->whereKey($linkId)->delete();
    }

    public function render(MemberService $members)
    {
        return view('livewire.member.profile.edit', [
            'districts' => District::orderBy('name')->get(),
            'completion' => $members->profileCompletionPercentage($this->member),
            'educationLevels' => EducationLevel::cases(),
            'educations' => $this->member->educations()->orderByDesc('graduated_year')->get(),
            'linkTypes' => MemberLink::TYPES,
            'memberLinks' => $this->member->links()->get(),
        ]);
    }
}
