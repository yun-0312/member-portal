<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use Illuminate\Http\Request;
use App\Models\ContentCategory;
use App\Http\Requests\ContentCategoryStoreRequest;
use App\Http\Requests\ContentCategoryUpdateRequest;

class ContentCategoryController extends BaseAdminMasterController
{
    protected string $modelClass = ContentCategory::class;
    protected string $routePrefix = 'content-categories';

    protected string $storeRequestClass = ContentCategoryStoreRequest::class;
    protected string $updateRequestClass = ContentCategoryUpdateRequest::class;

    protected string $sortColumn = 'sort_order';

    protected array $extraRelations = ['subcategories','roles'];

    protected function beforeStore(array $validated, Request $request): array {
        // sort_order が未入力なら自動採番
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = ContentCategory::getNextAvailableSortOrder();
        }

        return $validated;
    }

    //subCategoryを表示するためオーバーライド
    public function index(Request $request) {
        $query = $this->newModel()->query();

        if (!empty($this->extraRelations)) {
            $query->with($this->extraRelations);
        }

        $items = $query
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($request->input('per_page', 20))
            ->through(function ($category) {
                // コンテンツ自身の show_url
                $category->show_url = route("admin.{$this->routePrefix}.show", $category->id);

                // 紐づく subcategoryにshow_url を付与
                if ($category->subcategories) {
                    $category->subcategories->transform(function ($subcategory) {
                        $subcategory->show_url = route('admin.content-subcategories.show', $subcategory->id);
                        return $subcategory;
                    });
                }

                return $category;
            });

        $response = $items->toArray();
        $response['store_url'] = route("admin.{$this->routePrefix}.store");
        $response['subcategory_store_url'] = route("admin.content-subcategories.store");

        return response()->json($response);
    }

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $category = $this->findModel($id);

        // サブカテゴリが存在する場合は削除不可
        if ($category->subcategories()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはサブカテゴリが存在するため削除できません。',
            ], 422);
        }

        // コンテンツが存在する場合は削除不可
        if ($category->contents()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはコンテンツが存在するため削除できません。',
            ], 422);
        }
        $category->delete();

        return response()->json([
            'message' => 'カテゴリを削除しました',
        ]);
    }
}
