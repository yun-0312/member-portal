<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content;

class ContentPolicy
{
    //カテゴリごとの閲覧権限（target_roles)
    public function view(User $user, Content $content)
    {
        $roleId = $user->role_id;

        $contentRoles = $content->roles;
        $categoryRoles = optional($content->category)->roles;

        //Contentにroleが設定されている場合は優先
        if ($contentRoles->isNotEmpty()) {
            return $contentRoles->contains('id', $roleId);
        }

        if ($categoryRoles && $categoryRoles->isNotEmpty) {
            return $categoryRoles->contains('id', $roleId);
        }

        return true;
    }
}