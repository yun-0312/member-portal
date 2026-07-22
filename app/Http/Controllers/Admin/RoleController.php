<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use App\Models\Role;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;

class RoleController extends BaseAdminMasterController
{
    protected string $modelClass = Role::class;
    protected string $routePrefix = 'roles';

    protected string $storeRequestClass = RoleStoreRequest::class;
    protected string $updateRequestClass = RoleUpdateRequest::class;

    protected string $sortColumn = 'id';

        //URLとpermissionを追加するためオーバーライド
    public function show($id) {
        $role = $this->findModel($id);
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

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $role = $this->findModel($id);

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
