<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupCategory;
use App\Http\Requests\GroupCategoryStoreRequest;
use App\Http\Requests\GroupCategoryUpdateRequest;

class GroupCategoryController extends Controller
{
    public function index() {
        $categories =  GroupCategory::orderBy('sort_order')->get();

        $categories->transform(function ($category) {
            $category->show_url = route('admin.group-categories.show', $category->id);
            return $category;
        });

        return response()->json([
            'data' => $categories,
            'store_url' =>route('admin.group-categories.store'),
        ]);
    }

    public function show(GroupCategory $category) {
        $category->load('groups');

        $category->update_url = route('admin.group-categories.update', $category->id);
        $category->destroy_url = route('admin.group-categories.destroy', $category->id);
        $category->index_url = route('admin.group-categories.index');
        $category->group_store_url = route('admin.groups.store') . '?category_id=' . $category->id;

        $category->groups->transform(function ($group) {
            $group->update_url = route('admin.groups.update', $group->id);
            $group->destroy_url = route('admin.groups.destroy', $group->id);
            return $group;
        });

        return response()->json([
            'category' => $category,
        ]);
    }

    public function store(GroupCategoryStoreRequest $request) {
        $validated = $request->validated();

        $category = GroupCategory::create($validated);

        return response()->json([
            'message' => 'グループカテゴリを作成しました',
            'category' => $category,
        ], 201);
    }

    public function update(GroupCategoryUpdateRequest $request, GroupCategory $category) {
        $validated = $request->validated();

        $category->update($validated);

        return response()->json([
            'message' => 'グループカテゴリを更新しました',
            'category' => $category,
        ]);
    }

    public function destroy(GroupCategory $category) {
        // 子レコード（groups）が存在するかチェック
        if ($category->groups()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはグループが存在するため削除できません。',
            ], 422);
        }
        $category->delete();

        return response()->json([
            'message' => 'グループカテゴリを削除しました',
        ]);
    }
}
