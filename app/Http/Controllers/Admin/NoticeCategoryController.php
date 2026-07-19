<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoticeCategory;
use App\Http\Requests\NoticeCategoryStoreRequest;
use App\Http\Requests\NoticeCategoryUpdateRequest;

class NoticeCategoryController extends Controller
{
    public function index() {
        $categories = NoticeCategory::orderBy('sort_order', 'desc')->get();

        $categories->transform(function ($category) {
            $category->show_url = route('admin.notice-categories.show', $category->id);
            return $category;
        });

        return response()->json([
            'data' => $categories,
            'store_url' => route('admin.notice-categories.store'),
        ]);
    }

    public function show(NoticeCategory $noticeCategory) {
        return response()->json([
            'NoticeCategory' => [
                'id' => $noticeCategory->id,
                'name' => $noticeCategory->name,
                'slug' => $noticeCategory->slug,
                'sort_order' => $noticeCategory->sort_order,

                // URL 追加
                'update_url' => route('admin.notice-categories.update', $noticeCategory->id),
                'destroy_url' => route('admin.notice-categories.destroy', $noticeCategory->id),
                'index_url' => route('admin.notice-categories.index'),
                'notices_url' => route('notices.index', ['category' => $noticeCategory->slug]),
            ],
        ]);
    }

    public function store(NoticeCategoryStoreRequest $request) {
        $validated = $request->validated();

        $noticeCategory = NoticeCategory::create($validated);

        return response()->json([
            'message' => 'カテゴリを登録しました',
            'category' => $noticeCategory,
        ]);
    }

    public function update(NoticeCategoryUpdateRequest $request, NoticeCategory $noticeCategory) {
        $validated = $request->validated();

        $noticeCategory->update($validated);

        return response()->json([
            'message' => 'カテゴリを更新しました',
            'category' => $noticeCategory,
        ]);
    }

    public function destroy(NoticeCategory $noticeCategory) {
        // Notice が存在する場合は削除不可
        if ($noticeCategory->notices()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはお知らせが存在するため削除できません。',
            ], 422);
        }
        $noticeCategory->delete();

        return response()->json([
            'message' => 'カテゴリを削除しました',
        ]);
    }
}
