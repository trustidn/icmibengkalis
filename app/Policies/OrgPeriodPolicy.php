<?php

namespace App\Policies;

use App\Models\User;

class OrgPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organization.view');
    }

    public function create(User $user): bool
    {
        return $user->can('organization.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('organization.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('organization.manage');
    }
}
