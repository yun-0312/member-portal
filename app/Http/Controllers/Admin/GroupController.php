<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Requests\GroupUpdateRequest;

class GroupController extends Controller
{
    public function index(Request $request) {
        $query = Group::with('category')->orderBy('id', 'desc');

        //カテゴリ絞り込み
        if ($request->filled('category_id')) {
            $query->where('group_category_id', $request->category_id);
        }

        //名前検索
        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        $groups = $query->get();

        $groups->transform(function ($group) {
            $group->show_url = route('admin.groups.show', $group->id);
            return $group;
        });

        return response()->json([
            'data' => $groups,
            'store_url' => route('admin.groups.store'),
        ]);
    }

    public function show(Group $group) {
        $group->load('category');

        $group->update_url = route('admin.groups.update', $group->id);
        $group->destroy_url = route('admin.groups.destroy', $group->id);
        $group->index_url = route('admin.groups.index');

        return response()->json([
            'group' => $group,
        ]);
    }

    public function store(GroupStoreRequest $request) {
        $validated = $request->validated();
        if ($request->has('category_id')) {
            $validated['group_category_id'] = $request->category_id;
        }

        $group = Group::create($validated);

        return response()->json([
            'message' => 'グループを作成しました',
            'group' => $group,
        ], 201);
    }

    public function update(GroupUpdateRequest $request, Group $group) {
        $validated = $request->validated();

        $group->update($validated);

        return response()->json([
            'message' => 'グループを更新しました',
            'group' => $group,
        ]);
    }

    public function destroy(Group $group) {
        if ($group->contents()->exists()) {
            return response()->json([
                'message' => 'このグループはコンテンツに使用されているため削除できません。',
            ], 422);
        }

        $group->delete();

        return response()->json([
            'message' => 'グループを削除しました',
        ]);
    }

    }



}
