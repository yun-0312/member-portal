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
        $group->load('category', 'users');

        $group->users->transform(function ($user) use ($group) {
                $user->remove_url = route('admin.groups.users.destroy', [
                    'group' => $group->id,
                    'user' => $user->id,
                ]);
                return $user;
            });

        $group->search_user_url = route('admin.groups.users.search', $group->id);
        $group->add_user_url = route('admin.groups.users.store', ['group' => $group->id, 'user' => '__USER_ID__']);
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

        if ($group->users()->exists()) {
            return response()->json([
                'message' => 'このグループにはユーザーが所属しているため削除できません。',
            ], 422);
        }
        $group->delete();

        return response()->json([
            'message' => 'グループを削除しました',
        ]);
    }

    public function addUser(Group $group, User $user) {
        // medical_staff のユーザーは追加不可にする
        if ($user->role && $user->role->name === 'medical_staff') {
            return response()->json([
                'message' => '医療機関スタッフはこのグループに追加できません。',
            ], 422);
        }
        // 既に所属しているかチェック
        if ($group->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'このユーザーは既にグループに所属しています。',
            ], 422);
        }

        $group->users()->attach($user->id);

        return response()->json([
            'message' => 'グループにユーザーを追加しました',
            'user'    => $user,
        ]);
    }

    public function searchAddableUsers(Request $request, Group $group) {
        $keyword = $request->input('q');

        if (blank($keyword)) {
            return response()->json([]);
        }

        $users = User::query()
            // すでにこのグループに所属しているユーザーを除外する
            ->whereDoesntHave('groups', function ($q) use ($group) {
                $q->where('groups.id', $group->id);
            })
            // リレーションに 'medical_staff' を持つユーザーを除外
            ->whereDoesntHave('role', function ($q) {
                $q->where('name', '!=', 'medical_staff');
            })
            // 名前またはメールアドレスで部分一致検索
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
            })
            ->limit(20) // 候補が多すぎないように絞り込む
            ->get(['id', 'name', 'email']); // 必要な属性だけ取得

        return response()->json($users);
    }

    public function removeUser(Group $group, User $user) {
        // 該当のユーザーがグループに存在するかチェック（任意）
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'このユーザーは指定されたグループに所属していません。',
            ], 404);
        }

        $group->users()->detach($user->id);

        return response()->json([
            'message' => 'グループからユーザーを解除しました',
        ]);
    }



}
