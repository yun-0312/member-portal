<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait CheckRoleAccess {

    protected function hasRoleAccess(?Collection $roles, ?int $roleId) : bool
    {
        if (!$roles || $roles->isEmpty()) {
            return true;
        }

        return $roles->contains('id', $roleId);
    }
}