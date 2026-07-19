<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function isAdminOrStaff(User $user) {
        return in_array($user->role->name, ['admin', 'staff']);
    }
}
