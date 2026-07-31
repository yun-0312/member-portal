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
                'update_url' => "/admin/roles/$role->id/edit",
                'destroy_url' => "/admin/roles/$role->id",
                'index_url' => '/admin/roles',
                'add_permission_url' => "/admin/roles/$role->id/permissions",
                'permissions' => $role->permissions->map(function ($permission) use ($role) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'remove_url' => "/admin/roles/$role->id/permissions/$permission->id",
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
