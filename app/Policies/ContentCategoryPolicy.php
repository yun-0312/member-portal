<?php

namespace App\Policies;

use App\Models\User;
use APp\Models\ContentCategory;

class ContentCategoryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function view(User $user, ContentCategory $category)
    {
        if (in_array($user->role->name, ['admin', 'staff'])) {
            return true;
        }

        return $category->targetRoles()
            ->where('role_id', $user->role_id)
            ->exists();
    }
}
