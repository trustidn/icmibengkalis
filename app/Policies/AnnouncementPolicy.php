<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('announcements.manage') || $user->can('announcements.manage-any');
    }

    public function create(User $user): bool
    {
        return $user->can('announcements.manage') || $user->can('announcements.manage-any');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('announcements.manage-any')
            || ($user->can('announcements.manage') && $user->id === $announcement->created_by);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }
}
