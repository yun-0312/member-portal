<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
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

    protected array $extraRelations = ['category'];

    //削除時の制約チェックのためdestroyオーバーライド
    public function destroy($id) {
        $subcategory = $this->findModel($id);

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
