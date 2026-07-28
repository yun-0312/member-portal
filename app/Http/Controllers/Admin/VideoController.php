<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Models\Video;
use App\Http\Requests\VideoStoreRequest;
use App\Http\Requests\VideoUpdateRequest;

class VideoController extends BaseAdminContentController
{
    protected array $indexExtraRelations = ['files'];
    protected array $showExtraRelations = ['files', 'creator'];
    protected string $modelClass = Video::class;
    protected string $routePrefix = 'videos';

    protected string $storeRequestClass = VideoStoreRequest::class;
    protected string $updateRequestClass = VideoUpdateRequest::class;

     //Url追加のためオーバーライド
    public function show($id) {
        $item = $this->findModel($id);
        $this->authorize('view', $item);

        if (!empty($this->showExtraRelations)) {
            $item->load($this->showExtraRelations);
        }

        return response()->json([
            'item' => $item,
            'index_url' => "/admin/{$this->routePrefix}",
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
