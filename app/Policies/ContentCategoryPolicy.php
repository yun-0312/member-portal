<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContentCategory;
use App\Traits\CheckRoleAccess;

class ContentCategoryPolicy
{
    use CheckRoleAccess;
    /**
     * Create a new policy instance.
     */

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(User $user, ContentCategory $contentCategory): bool
    {
        if (in_array(optional($user->role)->name, ['admin', 'staff'], true)) {
            return true;
        }

        return $this->hasRoleAccess($contentCategory->roles, $user->role_id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ContentCategory $contentCategory): bool
    {
        return false;
    }

    public function delete(User $user, ContentCategory $contentCategory): bool
    {
        return false;
    }
}
