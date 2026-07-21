<?php

namespace App\Http\Controllers\Admin\Layout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SidebarController extends Controller
{
    public function index()
    {
        return [
            'sections' => [
                [
                    'title' => '管理',
                    'items' => [
                        ['label' => 'ダッシュボード', 'url' => route('admin.home.index')],
                        ['label' => 'お知らせ', 'url' => route('admin.notices.index')],
                        ['label' => 'コンテンツ', 'url' => route('admin.contents.index')],
                        ['label' => '会館スケジュール', 'url' => route('admin.schedules.index')],
                        ['label' => '講演会', 'url' => route('admin.workshops.index')],
                        ['label' => '動画', 'url' => route('admin.videos.index')],
                        ['label' => 'コールセンター', 'url' => route('admin.faqs.index')],
                        ['label' => '医療機関情報', 'url' => route('admin.medical_institutions.index')],
                        ['label' => 'ユーザー情報', 'url' => route('admin.users.index')],
                    ]
                ],
                [
                    'title' => '設定',
                    'items' => [
                        ['label' => 'ロール管理', 'url' => route('admin.roles.index')],
                        ['label' => '表示設定', 'url' => route('admin.permissions.index')],
                        ['label' => "コンテンツカテゴリー", 'url' => route('admin.roles.index'),]
                    ]
                ]
            ]
        ];
    }
}
