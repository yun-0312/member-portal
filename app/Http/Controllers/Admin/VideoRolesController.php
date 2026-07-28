<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Role;
use App\Http\Requests\RoleTargetableStoreRequest;

class VideoRolesController extends Controller
{
    public function store(RoleTargetableStoreRequest $request, Video $video)
    {
        $video->roles()->syncWithoutDetaching([$request->role_id]);

        return response()->json(['message' => 'ロールを追加しました']);
    }

    public function destroy(Video $video, Role $role)
    {
        $video->roles()->detach($role->id);

        return response()->json(['message' => 'ロールを削除しました']);
    }
}
