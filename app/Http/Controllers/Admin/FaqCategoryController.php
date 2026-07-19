<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Http\Requests\FaqCategoryStoreRequest;
use App\Http\Requests\FaqCategoryUpdateRequest;

class FaqCategoryController extends Controller
{
    public function index() {
        $categories = FaqCategory::orderBy('sort_order')->get();

        $categories->transform(function ($category) {
            $category->show_url = route('admin.faq-categories.show', $category->id);
            return $category;
        });

        return response()->json([
            'data' => $categories,
            'store_url' => route('admin.faq-categories.store'),
        ]);

    }

    public function show(FaqCategory $category) {
        $category->load('faqs');

        $category->update_url = route('admin.faq-categories.update', $category->id);
        $category->destroy_url = route('admin.faq-categories.destroy', $category->id);
        $category->index_url = route('admin.faq-categories.index');

        return response()->json([
            'category' => $category,
        ]);
    }

    public function store(FaqCategoryStoreRequest $request) {
        $validated = $request->validated();

        $category = FaqCategory::create($validated);

        return response()->json([
            'message' => 'FAQカテゴリを作成しました',
            'category' => $category,
        ], 201);
    }

    public function update(FaqCategoryUpdateRequest $request, FaqCategory $category) {
        $validated = $request->validated();

        $category->update($validated);

        return response()->json([
            'message' => 'FAQカテゴリを更新しました',
            'category' => $category,
        ]);
    }

    public function destroy(FaqCategory $category) {
        $category->delete();

        return response()->json([
            'message' => 'FAQカテゴリを削除しました',
        ]);
    }
}
