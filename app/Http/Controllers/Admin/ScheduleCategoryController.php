<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use App\Models\ScheduleCategory;
use App\Http\Requests\ScheduleCategoryStoreRequest;
use App\Http\Requests\ScheduleCategoryUpdateRequest;

class ScheduleCategoryController extends BaseAdminMasterController
{

    protected string $modelClass = ScheduleCategory::class;
    protected string $routePrefix = 'schedule-categories';

    protected string $storeRequestClass = ScheduleCategoryStoreRequest::class;
    protected string $updateRequestClass = ScheduleCategoryUpdateRequest::class;

    protected string $sortColumn = 'id';

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $category = $this->findModel($id);

        if ($category->schedules()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはスケジュールが存在するため削除できません。',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'カテゴリを削除しました',
        ]);

    }
}
