<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;

class PermissionController extends Controller
{
    public function index() {
        $permissions = Permission::orderBy('id')->get();

        $permissions->transform(function ($permission) {
            $permission->show_url = route('admin.permissions.show', $permission->id);
            return $permission;
        });

        return response()->json([
            'data' => $permissions,
            'store_url' => route('admin.permissions.store'),
        ]);
    }

    public function show(Permission $permission) {
        $permission->load('roles');

        return response()->json([
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'update_url' => route('admin.permissions.update', $permission->id),
                'destroy_url' => route('admin.permissions.destroy', $permission->id),
                'add_role_url' => route('admin.role-permissions.store', $permission->id),
                'index_url' => route('admin.permissions.index'),
                'roles' => $permission->roles->map(function ($role) use ($permission) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'remove_url' => route('admin.role-permissions.destroy', ['role' => $role->id, 'permission' => $permission->id]),
                    ];
                }),
            ],
        ]);
    }

    public function store(PermissionStoreRequest $request) {
        $validated = $request->validated();

        $permission = Permission::create($validated);

        return response()->json([
            'message' => 'パーミッションを登録しました',
            'permission' => $permission,
        ]);
    }

    public function update(PermissionUpdateRequest $request, Permission $permission) {
        $validated = $request->validated();

        $permission->update($validated);

        return response()->json([
            'message' => 'パーミッションを更新しました',
            'permission' => $permission,
        ]);
    }

    public function destroy(Permission $permission) {
        if ($permission->roles()->exists()) {
            return response()->json([
                'message' => 'このパーミッションは使用中のため削除できません。',
            ], 422);
        }
        $permission->delete();

        return response()->json([
            'message' => 'パーミッションを削除しました',
        ]);
    }
}
