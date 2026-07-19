<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentCategory;
use App\Http\Requests\ContentCategoryStoreRequest;
use App\Http\Requests\ContentCategoryUpdateRequest;

class ContentCategoryController extends Controller
{
    public function index() {
        $categories = ContentCategory::orderBy('sort_order')->get();

        $categories->transform(function ($category) {
            $category->show_url = route('admin.content-categories.show', $category->id);
            return $category;
        });

        return response()->json([
            'data' => $categories,
            'store_url' => route('admin.content-categories.store'),
        ]);
    }

    public function show(ContentCategory $category) {
        $category->load('subcategories');

        $category->update_url = route('admin.content-categories.update', $category->id);
        $category->destroy_url = route('admin.content-categories.destroy', $category->id);
        $category->index_url = route('admin.content-categories.index');

        return response()->json([
            'category' => $category,
        ]);
    }

    public function store(ContentCategoryStoreRequest $request) {
        $validated = $request->validated();

        $category = ContentCategory::create($validated);

        return response()->json([
            'message' => 'カテゴリを作成しました',
            'category' => $category,
        ], 201);
    }

    public function update(ContentCategoryUpdateRequest $request, ContentCategory $category) {
        $validated = $request->validated();

        $category->update($validated);

        return response()->json([
            'message' => 'カテゴリを更新しました',
            'category' => $category,
        ]);
    }

    public function destroy(ContentCategory $category) {
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
