<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Role;
use App\Http\Requests\RoleTargetableStoreRequest;

class ContentRolesController extends Controller
{
    public function store(RoleTargetableStoreRequest $request, Content $content)
    {
        $content->roles()->syncWithoutDetaching([$request->role_id]);

        return response()->json(['message' => 'ロールを追加しました']);
    }

    public function destroy(Content $content, Role $role)
    {
        $content->roles()->detach($role->id);

        return response()->json(['message' => 'ロールを削除しました']);
    }
}
