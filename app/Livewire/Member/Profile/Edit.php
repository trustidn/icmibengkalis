<?php

namespace App\Livewire\Member\Profile;

use App\Models\District;
use App\Models\Member;
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

    public string $website = '';

    public string $whatsapp = '';

    public string $linkedin = '';

    public bool $show_contact_public = false;

    public $photo = null;

    public bool $saved = false;

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
        $this->website = (string) ($member->social_links['website'] ?? '');
        $this->whatsapp = (string) ($member->social_links['whatsapp'] ?? '');
        $this->linkedin = (string) ($member->social_links['linkedin'] ?? '');
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
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'show_contact_public' => ['boolean'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $members->update($this->member, [
            'full_name' => $validated['full_name'],
            'title_prefix' => $validated['title_prefix'],
            'title_suffix' => $validated['title_suffix'],
            'gender' => $validated['gender'],
            'birth_place' => $validated['birth_place'],
            'birth_date' => $validated['birth_date'],
            'address' => $validated['address'],
            'district_id' => $validated['district_id'],
            'institution' => $validated['institution'],
            'profession' => $validated['profession'],
            'expertise' => $validated['expertise'],
            'bio' => $validated['bio'],
            'social_links' => array_filter([
                'website' => $validated['website'] ?: null,
                'whatsapp' => $validated['whatsapp'] ?: null,
                'linkedin' => $validated['linkedin'] ?: null,
            ]),
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

    public function render(MemberService $members)
    {
        return view('livewire.member.profile.edit', [
            'districts' => District::orderBy('name')->get(),
            'completion' => $members->profileCompletionPercentage($this->member),
        ]);
    }
}
