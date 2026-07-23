<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content;
use App\Traits\CheckRoleAccess;

class ContentPolicy
{
    use CheckRoleAccess;
    protected function isStaffOrAdmin(User $user): bool
    {
        $roleName = optional($user->role)->name;

        return in_array($roleName, ['admin', 'staff'], true);
    }

    public function viewAny(User $user): bool
    {
        return $this->isStaffOrAdmin($user);
    }

    public function view(User $user, Content $content)
    {
        if ($this->isStaffOrAdmin($user)) {
            return true;
        }

        $roleId = $user->role_id;
        $contentRoles = $content->roles;
        $categoryRoles = optional($content->category)->roles;

        //Contentにroleが設定されている場合は優先
        if ($contentRoles->isNotEmpty()) {
            return $contentRoles->contains('id', $roleId);
        }

        if ($categoryRoles && $categoryRoles->isNotEmpty()) {
            return $categoryRoles->contains('id', $roleId);
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $this->isStaffOrAdmin($user);
    }

    public function update(User $user, Content $content): bool
    {
        return $this->isStaffOrAdmin($user);
    }

    public function delete(User $user, Content $content): bool
    {
        return $this->isStaffOrAdmin($user);
    }
}