<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('agenda.manage') || $user->can('agenda.manage-any');
    }

    public function create(User $user): bool
    {
        return $user->can('agenda.manage') || $user->can('agenda.manage-any');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->can('agenda.manage-any')
            || ($user->can('agenda.manage') && $user->id === $event->created_by);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
