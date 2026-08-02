<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use Illuminate\Http\Request;
use App\Models\ContentSubcategory;
use App\Http\Requests\ContentSubcategoryStoreRequest;
use App\Http\Requests\ContentSubcategoryUpdateRequest;

class ContentSubcategoryController extends BaseAdminMasterController
{
    protected string $modelClass = ContentSubcategory::class;
    protected string $routePrefix = 'content-subcategories';

    protected string $storeRequestClass = ContentSubcategoryStoreRequest::class;
    protected string $updateRequestClass = ContentSubcategoryUpdateRequest::class;

    protected string $sortColumn = 'sort_order';

    protected array $extraRelations = ['category', 'roles'];

    protected function beforeStore(array $validated, Request $request): array {
        // sort_order が未入力なら自動採番
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = ContentSubcategory::getNextAvailableSortOrder();
        }

        return $validated;
    }

    //indexURL変更のためオーバーライド
    public function show($id) {
        $item = $this->findModel($id);

        if (!empty($this->extraRelations)) {
            $item->load($this->extraRelations);
        }

        return response()->json([
            'item' => $item,
            'index_url' => "/admin/content-categories",
            'update_url' => "/admin/$this->routePrefix/$item->id/edit",
            'delete_url' => "/admin/$this->routePrefix/$item->id",
        ]);
    }

    //削除時の制約チェックのためdestroyオーバーライド
    public function destroy($id) {
        $subcategory = $this->findModel($id);

        // 子サブカテゴリが存在する場合は削除不可
        if ($subcategory->children()->exists()) {
            return response()->json([
                'message' => 'このサブカテゴリには下位（子）のサブカテゴリが存在するため削除できません。先に子サブカテゴリを削除してください。',
            ], 422);
        }

        // コンテンツが存在する場合は削除不可
        if ($subcategory->contents()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはコンテンツが存在するため削除できません。',
            ], 422);
        }
        $subcategory->delete();

        return response()->json([
            'message' => 'サブカテゴリを削除しました',
        ]);
    }

}
