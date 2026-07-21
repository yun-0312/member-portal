<?php

namespace App\Http\Controllers\Layout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HeaderController extends Controller
{
    public function index(Request $request) {
        return [
            'user' => $request->user(),
            'menu' => [
                ['label' => 'ホーム', 'url' => route('home.index')],
                ['label' => '会員情報', 'url' => route('auth.me')],
                ['label' => 'ログアウト', 'url' => route('auth.logout')],
            ],
        ];
    }
}
