<?php

namespace App\Policies;

use App\Models\User;
use App\Models\video;
use App\Traits\CheckRoleAccess;

class VideoPolicy
{
    use CheckRoleAccess;
    /**
     * Create a new policy instance.
     */
    protected function isStaffOrAdmin(User $user): bool
    {
        $roleName = optional($user->role)->name;

        return in_array($roleName, ['admin', 'staff'], true);
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(User $user, Video $video): bool
    {
        if ($this->isStaffOrAdmin($user)) {
            return true;
        }

        return $this->hasRoleAccess($video->roles, $user->role_id);
    }

    public function create(User $user): bool
    {
        return $this->isStaffOrAdmin($user);
    }

    public function update(User $user, Video $video): bool
    {
        return $this->isStaffOrAdmin($user);
    }

    public function delete(User $user, Video $video): bool
    {
        return $this->isStaffOrAdmin($user);
    }
}
