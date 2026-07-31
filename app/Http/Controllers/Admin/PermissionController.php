<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use App\Models\Permission;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;

class PermissionController extends BaseAdminMasterController
{
    protected string $modelClass = Permission::class;
    protected string $routePrefix = 'permissions';

    protected string $storeRequestClass = PermissionStoreRequest::class;
    protected string $updateRequestClass = PermissionUpdateRequest::class;

    protected string $sortColumn = 'id';

    protected array $extraRelations = ['roles'];


    //URLを追加するためオーバーライド
    public function show($id) {
        $permission = $this->findModel($id);

        if (!empty($this->extraRelations)) {
            $permission->load($this->extraRelations);
        }

        // $roleId = $permission->roles->first()?->id ?? null;

        return response()->json([
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'update_url' => "/admin/permissions/$permission->id/edit",
                'destroy_url' => "admin/permissions/$permission->id",
                'add_role_url' => "/admin/roles/{role_id}/permissions/$permission->id",
                'index_url' => '/admin/permissions',
                'roles' => $permission->roles->map(function ($role) use ($permission) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'remove_url' => "/admin/roles/$role->id/permissions/$permission->id",
                    ];
                }),
            ],
        ]);
    }

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $permission = $this->findModel($id);

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
