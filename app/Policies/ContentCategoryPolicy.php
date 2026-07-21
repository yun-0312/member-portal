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
    public function view(User $user, ContentCategory $category)
    {
        return $this->hasRoleAccess($category->roles, $user->role_id);
    }
}
