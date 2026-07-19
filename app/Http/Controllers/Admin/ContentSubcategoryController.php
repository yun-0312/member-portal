<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentSubcategory;
use App\Http\Requests\ContentSubcategoryStoreRequest;
use App\Http\Requests\ContentSubcategoryUpdateRequest;

class ContentSubcategoryController extends Controller
{
    public function index() {
        $subcategories = ContentSubcategory::with('category')
            ->orderBy('sort_order')
            ->get();

        $subcategories->transform(function ($subcategory) {
            $subcategory->show_url = route('admin.content-subcategories.show', $subcategory->id);
            return $subcategory;
        });

        return response()->json([
            'data' => $subcategories,
            'store_url' => route('admin.content-subcategories.store'),
        ]);
    }

    public function show(ContentSubcategory $subcategory) {
        $subcategory->load('category');

        $subcategory->update_url = route('admin.content-subcategories.update', $subcategory->id);
        $subcategory->destroy_url = route('admin.content-subcategories.destroy', $subcategory->id);
        $subcategory->index_url = route('admin.content-subcategories.index');

        return response()->json([
            'subcategory' => $subcategory,
        ]);
    }

    public function store(ContentSubcategoryStoreRequest $request) {
        $validated = $request->validated();

        $subcategory = ContentSubcategory::create($validated);

        return response()->json([
            'message' => 'サブカテゴリを作成しました',
            'subcategory' => $subcategory,
        ], 201);
    }

    public function update(ContentSubcategoryUpdateRequest $request, ContentSubcategory $subcategory) {
        $validated = $request->validated();

        $subcategory->update($validated);

        return response()->json([
            'message' => 'サブカテゴリを更新しました',
            'subcategory' => $subcategory,
        ]);

    }

    public function destroy(ContentSubcategory $subcategory) {
        $subcategory->delete();

        return response()->json([
            'message' => 'サブカテゴリを削除しました',
        ]);
    }
}
