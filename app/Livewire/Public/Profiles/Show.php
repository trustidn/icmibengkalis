<?php

namespace App\Livewire\Public\Profiles;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Membership\MemberService;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Show extends Component
{
    public string $identifier;

    public function mount(string $identifier, MemberService $members): void
    {
        $member = $members->findPublicProfile($identifier);

        abort_unless($member, Response::HTTP_NOT_FOUND);

        $this->identifier = $identifier;
    }

    public function render(MemberService $members)
    {
        $member = $members->findPublicProfile($this->identifier);

        $writings = $member->user_id
            ? Post::query()
                ->where('author_id', $member->user_id)
                ->where('status', PostStatus::Published)
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->limit(6)
                ->get()
            : collect();

        return view('livewire.public.profiles.show', [
            'member' => $member,
            'writings' => $writings,
            'profileUrl' => route('profiles.show', $member->slug ?? $member->id),
        ])->layout('components.layouts.public', [
            'metaTitle' => $member->full_name.' — '.config('app.name'),
            'metaDescription' => $member->bio ? Str::limit(strip_tags($member->bio), 160) : "Profil {$member->full_name}, anggota ICMI Kabupaten Bengkalis.",
        ]);
    }
}
