<?php

namespace App\Policies;

use App\Models\Album;
use App\Models\User;

class AlbumPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gallery.manage') || $user->can('gallery.manage-any');
    }

    public function create(User $user): bool
    {
        return $user->can('gallery.manage') || $user->can('gallery.manage-any');
    }

    public function update(User $user, Album $album): bool
    {
        return $user->can('gallery.manage-any')
            || ($user->can('gallery.manage') && $user->id === $album->created_by);
    }

    public function delete(User $user, Album $album): bool
    {
        return $this->update($user, $album);
    }
}
