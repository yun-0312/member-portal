<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workshop;
use App\Policies\BasePolicy;

class WorkshopPolicy extends BasePolicy
{
    //閲覧権限（target_roles)
    public function view(User $user, Workshop $workshop)
    {
        return $workshop->targetRoles()
            ->where('role_id', $user->role_id)
            ->exists();
    }

}