<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminMasterController;
use Illuminate\Http\Request;
use App\Models\FaqCategory;
use App\Http\Requests\FaqCategoryStoreRequest;
use App\Http\Requests\FaqCategoryUpdateRequest;

class FaqCategoryController extends BaseAdminMasterController
{
    protected string $modelClass = FaqCategory::class;
    protected string $routePrefix = 'faq-categories';

    protected string $storeRequestClass = FaqCategoryStoreRequest::class;
    protected string $updateRequestClass = FaqCategoryUpdateRequest::class;

    protected string $sortColumn = 'sort_order';

    protected function beforeStore(array $validated, Request $request): array {
        // sort_order が未入力なら自動採番
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = FaqCategory::getNextAvailableSortOrder();
        }

        return $validated;
    }

    //削除時の制約チェックのためオーバーライド
    public function destroy($id) {
        $category = $this->findModel($id);

        // コンテンツが存在する場合は削除不可
        if ($category->faqs()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはコンテンツが存在するため削除できません。',
            ], 422);
        }
        $category->delete();

        return response()->json([
            'message' => 'FAQカテゴリを削除しました',
        ]);
    }
}
