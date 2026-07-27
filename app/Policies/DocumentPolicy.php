<?php

namespace App\Policies;

use App\Enums\DocumentAccessLevel;
use App\Enums\DocumentGranteeType;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('archive.view');
    }

    public function view(?User $user, Document $document): bool
    {
        if ($document->access_level === DocumentAccessLevel::Publik) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->can('archive.view')) {
            return true;
        }

        return match ($document->access_level) {
            DocumentAccessLevel::Anggota => $user->member !== null,
            DocumentAccessLevel::Pengurus => $user->member?->isPengurus() === true,
            DocumentAccessLevel::Terbatas => $this->hasGrant($user, $document),
            default => false,
        };
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

    public function manageAccess(User $user): bool
    {
        return $user->can('archive.manage-access');
    }

    private function hasGrant(User $user, Document $document): bool
    {
        $roleIds = $user->roles->pluck('id');
        $orgUnitId = $user->member?->orgAssignments()->value('org_unit_id');

        foreach ($document->permissions as $permission) {
            $matches = match ($permission->grantee_type) {
                DocumentGranteeType::User => $permission->grantee_id === $user->id,
                DocumentGranteeType::Role => $roleIds->contains($permission->grantee_id),
                DocumentGranteeType::OrgUnit => $orgUnitId !== null && $permission->grantee_id === $orgUnitId,
            };

            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
