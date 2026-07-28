<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Role;
use App\Http\Requests\RoleTargetableStoreRequest;

class NoticeRolesController extends Controller
{
    public function store(RoleTargetableStoreRequest $request, Notice $notice) {
        $this->authorize('update', $notice);
        $notice->roles()->syncWithoutDetaching([$request->role_id]);

        return response()->json(['message' => 'ロールを追加しました']);
    }

    public function destroy(Notice $notice, Role $role) {
        $this->authorize('update', $notice);
        $notice->roles()->detach($role->id);

        return response()->json(['message' => 'ロールを削除しました']);
    }
}
