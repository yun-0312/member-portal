<?php

namespace App\Http\Controllers\Admin\Layout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HeaderController extends Controller
{
    public function index() {
        return [
            'menu' => [
                ['label' => 'ダッシュボード', 'url' => route('admin.home.index')],
                ['label' => 'お知らせ', 'url' => route('admin.notices.index')],
                ['label' => 'コンテンツ', 'url' => route('admin.contents.index')],
                ['label' => '会館予約', 'url' => route('admin.schedules.index')],
            ],
        ];
    }
}
