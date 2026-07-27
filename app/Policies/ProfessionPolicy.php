<?php

namespace App\Policies;

use App\Models\User;

class ProfessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('members.view');
    }

    public function create(User $user): bool
    {
        return $user->can('members.update');
    }

    public function update(User $user): bool
    {
        return $user->can('members.update');
    }

    public function delete(User $user): bool
    {
        return $user->can('members.delete');
    }
}
