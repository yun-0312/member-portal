<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notice;
use App\Traits\CheckRoleAccess;

class NoticePolicy
{
    use CheckRoleAccess;
    /**
     * Create a new policy instance.
     */
    public function view(User $user, Notice $notice)
    {
        return $this->hasRoleAccess($notice->roles, $user->role_id);
    }
}
