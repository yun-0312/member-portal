<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $allLinks = [
            'users' => [
                'name' => 'ユーザー管理',
                'url' => '/admin/users',
            ],
            'medical_institutions' => [
                'name' => '医療機関管理',
                'url' => '/admin/medical-institutions',
            ],
            'roles' => [
                'name' => 'ロール管理',
                'url' => '/admin/roles',
            ],
            'content_categories' => [
                'name' => 'コンテンツカテゴリー管理',
                'url' => '/admin/content-categories',
            ],
            'faq_categories' => [
                'name' => 'コールセンター問い合わせ報告書カテゴリー管理',
                'url' => '/admin/faq-categories',
            ],
            'schedule_category' => [
                'name' => 'スケジュールカテゴリー管理',
                'url' => '/admin/schedule-categories',
            ],
            'rooms' => [
                'name' => '会議室管理',
                'url' => '/admin/rooms',
            ],
        ];

        // 1. admin の場合は全リンクを返却
        if ($user->isAdmin()) {
            return response()->json([
                'links' => array_values($allLinks)
            ]);
        }

        // 2. staff の場合は users と medical_institutions のみ返却
        $roleName = $user->role?->name;
        if ($roleName === 'staff') {
            $staffKeys = ['users', 'medical_institutions'];
            $staffLinks = array_intersect_key($allLinks, array_flip($staffKeys));

            return response()->json([
                'links' => array_values($staffLinks)
            ]);
        }

        // 3. その他のロール
        return response()->json(['links' => []], 403);
    }
}