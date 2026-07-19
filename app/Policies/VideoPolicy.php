<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Video;
use App\Policies\BasePolicy;

class VideoPolicy extends BasePolicy
{
    //閲覧権限（target_roles)
    public function view(User $user, Video $video)
    {
        return $video->targetRoles()
            ->where('role_id', $user->role_id)
            ->exists();
    }

}