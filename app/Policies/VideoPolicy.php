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
    public function view(User $user, Video $video)
    {
        return $this->hasRoleAccess($video->roles, $user->role_id);
    }
}
