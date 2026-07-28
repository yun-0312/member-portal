<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Models\Notice;
use App\Http\Requests\NoticeStoreRequest;
use App\Http\Requests\NoticeUpdateRequest;

class NoticeController extends BaseAdminContentController
{
    protected array $indexExtraRelations = ['category', 'files', 'roles'];
    protected array $showExtraRelations = ['category', 'files', 'roles'];

    protected string $modelClass = Notice::class;
    protected string $routePrefix = 'notices';

    protected string $storeRequestClass = NoticeStoreRequest::class;
    protected string $updateRequestClass = NoticeUpdateRequest::class;

    //URL編集のためオーバーライド
    public function show($id) {
        $item = $this->findModel($id);
        $this->authorize('view', $item);

        if (!empty($this->showExtraRelations)) {
            $item->load($this->showExtraRelations);
        }

        return response()->json([
            'item' => $item,
            'index_url' => "/admin/{$this->routePrefix}?category={$item->category->slug}",
            'update_url' => "/admin/{$this->routePrefix}/{$item->id}",
            'delete_url' => "/admin/{$this->routePrefix}/{$item->id}",
            'role_targetable_url' => "/admin/{$this->routePrefix}/{$item->id}/roles",
            'roles' => $item->roles->map(function ($role) use ($item) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'destroy_url' => "/admin/{$this->routePrefix}/{$item->id}/roles/{$role->id}",
                ];
            }),
        ]);
    }

}
