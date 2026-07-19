<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\BasePolicy;

class UserPolicy extends BasePolicy
{
    public function view(User $user, User $target) {
        if (in_array($user->role->name, ['admin', 'staff'])) {
            return true;
        }

        if (in_array($user->role->name, ['member', 'director'])) {
            return $user->medical_institution_id === $target->medical_institution_id;
        }

        if ($user->role->name === 'medical_staff') {
            return $user->id === $target->id;
        }

        return false;
    }

}