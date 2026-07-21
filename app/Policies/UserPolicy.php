<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $target) {

        $role = optional($user->role)->name;

        if (in_array($role, ['member', 'director'], true)) {
            return $user->medical_institution_id === $target->medical_institution_id;
        }

        if ($role->name === 'medical_staff') {
            return $user->id === $target->id;
        }

        return false;
    }

}