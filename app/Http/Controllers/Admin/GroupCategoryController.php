<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\GroupCategory;
use App\Http\Requests\GroupCategoryStoreRequest;
use App\Http\Requests\GroupCategoryUpdateRequest;

class GroupCategoryController extends BaseAdminMasterController
{
    protected string $modelClass = GroupCategory::class;
    protected string $routePrefix = 'group-categories';

    protected string $storeRequestClass = GroupCategoryStoreRequest::class;
    protected string $updateRequestClass = GroupCategoryUpdateRequest::class;

    protected string $sortColumn = 'sort_order';

    protected array $extraRelations = ['groups'];

    protected function beforeStore(array $validated, Request $request): array {
        // sort_order が未入力なら自動採番
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = GroupCategory::getNextAvailableSortOrder();
        }

        return $validated;
    }

    //URL追加のためオーバーライド
    public function show($id): JsonResponse {
        $category = $this->findModel($id);

        if (!empty($this->extraRelations)) {
            $category->load($this->extraRelations);
        }

        $category->store_url = route('admin.groups.store');

        if ($category->relationLoaded('groups')) {
            $category->groups->transform(function ($group) {
                $group->update_url = route('admin.groups.update', $group->id);
                $group->destroy_url = route('admin.groups.destroy', $group->id);
                return $group;
            });
        }

        return response()->json([
            'category' => $category,
        ]);
    }

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $category = $this->findModel($id);
        // 子レコード（groups）が存在するかチェック
        if ($category->groups()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはグループが存在するため削除できません。',
            ], 422);
        }
        $category->delete();

        return response()->json([
            'message' => 'サブカテゴリを削除しました',
        ]);
    }
}
