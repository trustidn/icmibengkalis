<?php

namespace App\Policies;

use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contact.view');
    }

    public function update(User $user): bool
    {
        return $user->can('contact.reply');
    }
}
