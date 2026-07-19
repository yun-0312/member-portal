<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content;

class ContentPolicy
{
    //カテゴリごとの閲覧権限（target_roles)
    public function view(User $user, Content $content)
    {
        return $content->category
            ->targetRoles()
            ->where('role_id', $user->role_id)
            ->exists();
    }
}