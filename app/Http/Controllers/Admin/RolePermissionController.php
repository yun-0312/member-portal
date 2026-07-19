<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Http\Requests\RolePermissionStoreRequest;

class RolePermissionController extends Controller
{
    public function index(Role $role) {
        return $role->permissions()->orderBy('id')->get();
    }

    public function store(RolePermissionStoreRequest $request, Role $role) {
        $validated = $request->validated();

        $role->permissions()->attach($validated['permission_id']);

        return response()->json([
            'message' => '権限を追加しました',
        ], 201);
    }

    public function destroy(Role $role, Permission $permission) {
        $role->permissions()->detach($permission->id);

        return response()->json([
            'message' => '権限を削除しました',
        ]);
    }
}
