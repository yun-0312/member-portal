<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleTargetableController extends Controller
{
protected function getTargetable(string $type, int|string $id)
    {
        // 1. タイプ文字列（URL用）とモデルクラスの対応マップ
        $map = [
            'content-categories'    => \App\Models\ContentCategory::class,
            'content-subcategories' => \App\Models\ContentSubcategory::class,
            'notice-categories'     => \App\Models\NoticeCategory::class,
            // 必要に応じて他の対象（Video 等）もここに追加するだけで対応可能！
        ];

        if (!isset($map[$type])) {
            abort(404, '指定されたカテゴリータイプが存在しません。');
        }

        $modelClass = $map[$type];
        return $modelClass::findOrFail($id);
    }

    /**
     * 紐づいているロール一覧の取得 (GET)
     */
    public function index(string $type, $id)
    {
        $targetable = $this->getTargetable($type, $id);

        return response()->json($targetable->roles()->orderBy('id')->get());
    }

    /**
     * ロールの追加/アタッチ (POST)
     */
    public function store(Request $request, string $type, $id)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $targetable = $this->getTargetable($type, $id);

        // すでに紐づいていない場合のみ追加 (syncWithoutDetaching)
        $targetable->roles()->syncWithoutDetaching([$validated['role_id']]);

        return response()->json([
            'message' => 'ロールを追加しました',
        ], 201);
    }

    /**
     * ロールの削除/デタッチ (DELETE)
     */
    public function destroy(string $type, $id, $roleId)
    {
        $targetable = $this->getTargetable($type, $id);

        $targetable->roles()->detach($roleId);

        return response()->json([
            'message' => 'ロールを削除しました',
        ]);
    }
}
