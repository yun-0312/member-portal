<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;

class RoleController extends Controller
{
    public function index() {
        $roles = Role::orderBy('id')->get();

        $roles->transform(function ($role) {
            $role->show_url = route('admin.roles.show', $role->id);
            return $role;
        });

        return response()->json([
            'data' => $roles,
            'store_url' => route('admin.roles.store'),
        ]);
    }

    public function show(Role $role) {
        $role->load('permissions');

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'update_url' => route('admin.roles.update', $role->id),
                'destroy_url' => route('admin.roles.destroy', $role->id),
                'index_url' => route('admin.roles.index'),
                'add_permission_url' => route('admin.role-permissions.store', $role->id),
                'permissions' => $role->permissions->map(function ($permission) use ($role) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'remove_url' => route(
                            'admin.role-permissions.destroy',
                            ['role' => $role->id, 'permission' => $permission->id]
                        ),
                    ];
                }),
            ],
        ]);
    }


    public function store(RoleStoreRequest $request) {
        $validated = $request->validated();

        $role = Role::create($validated);

        return response()->json([
            'message' => 'ロールを作成しました',
            'role' => $role,
        ], 201);
    }

    public function update(RoleUpdateRequest $request, Role $role) {
        $validated = $request->validated();

        $role->update($validated);

        return response()->json([
            'message' => 'ロールを更新しました',
            'role' => $role,
        ]);
    }

    public function destroy(Role $role) {
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'このロールは使用中のため削除できません。',
            ], 422);
        }

        if ($role->permissions()->exists()) {
            return response()->json([
                'message' => 'このロールは使用中のため削除できません',
            ], 422);
        }
        $role->delete();

        return response()->json([
            'message' => 'ロールを削除しました',
        ]);
    }
}
