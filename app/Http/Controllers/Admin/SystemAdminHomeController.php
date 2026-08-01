<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemAdminHomeController extends Controller
{
    public function index(Request $request)
    {

        // ログインユーザーが system_admin でない場合は 403 エラーを返す
        if ($request->user()?->role?->name !== 'system_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json([
            'links' => [
                ['name' => 'お知らせカテゴリー管理', 'url' => '/admin/notice-categories'],
                ['name' => '権限管理', 'url' => '/admin/permissions'],
            ]
        ]);
    }
}
