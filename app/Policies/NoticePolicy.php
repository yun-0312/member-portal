<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notice;
use App\Policies\BasePolicy;

class NoticePolicy extends BasePolicy
{
    //閲覧権限（target_roles)
    public function view(User $user, Notice $notice)
    {
        return $notice->targetRoles()
            ->where('role_id', $user->role_id)
            ->exists();
    }
}