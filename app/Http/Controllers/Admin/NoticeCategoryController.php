<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use Illuminate\Http\Request;
use App\Models\NoticeCategory;
use App\Http\Requests\NoticeCategoryStoreRequest;
use App\Http\Requests\NoticeCategoryUpdateRequest;

class NoticeCategoryController extends BaseAdminMasterController
{
    protected string $modelClass = NoticeCategory::class;
    protected string $routePrefix = 'notice-categories';

    protected string $storeRequestClass = NoticeCategoryStoreRequest::class;
    protected string $updateRequestClass = NoticeCategoryUpdateRequest::class;

    protected string $sortColumn = 'sort_order';

    protected function beforeStore(array $validated, Request $request): array {
        // sort_order が未入力なら自動採番
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = NoticeCategory::getNextAvailableSortOrder();
        }

        return $validated;
    }

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $category = $this->findModel($id);

        // Notice が存在する場合は削除不可
        if ($category->notices()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはお知らせが存在するため削除できません。',
            ], 422);
        }
        $category->delete();

        return response()->json([
            'message' => 'カテゴリを削除しました',
        ]);
    }
}
