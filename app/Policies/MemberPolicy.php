<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('members.view');
    }

    public function view(User $user, Member $member): bool
    {
        return $user->can('members.view') || $user->id === $member->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('members.create');
    }

    public function update(User $user, Member $member): bool
    {
        return $user->can('members.update') || $user->id === $member->user_id;
    }

    public function delete(User $user): bool
    {
        return $user->can('members.delete');
    }

    public function import(User $user): bool
    {
        return $user->can('members.import');
    }
}
