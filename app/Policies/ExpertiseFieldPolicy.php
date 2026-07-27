<?php

namespace App\Policies;

use App\Models\User;

class ExpertiseFieldPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expertise.view');
    }

    public function create(User $user): bool
    {
        return $user->can('expertise.manage-fields');
    }

    public function update(User $user): bool
    {
        return $user->can('expertise.manage-fields');
    }

    public function delete(User $user): bool
    {
        return $user->can('expertise.manage-fields');
    }
}
