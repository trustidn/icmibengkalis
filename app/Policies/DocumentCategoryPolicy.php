<?php

namespace App\Policies;

use App\Models\User;

class DocumentCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('archive.view');
    }

    public function create(User $user): bool
    {
        return $user->can('archive.create');
    }

    public function update(User $user): bool
    {
        return $user->can('archive.update');
    }

    public function delete(User $user): bool
    {
        return $user->can('archive.delete');
    }
}
