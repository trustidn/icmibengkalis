<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('publishing.view') || $user->member !== null;
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('publishing.view') || $user->id === $post->author_id;
    }

    public function create(User $user): bool
    {
        return $user->can('publishing.create') || $user->member !== null;
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can('publishing.update')) {
            return true;
        }

        return $user->id === $post->author_id
            && in_array($post->status, [PostStatus::Draft, PostStatus::Rejected], true);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('publishing.delete');
    }

    public function review(User $user): bool
    {
        return $user->can('publishing.review');
    }

    public function publish(User $user): bool
    {
        return $user->can('publishing.publish');
    }
}
