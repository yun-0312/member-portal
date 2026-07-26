<?php

namespace App\Http\Controllers\Layout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HeaderController extends Controller
{
    public function index(Request $request) {
        return [
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'role' => $request->user()->role->name,
            ],
            'menu' => [
                ['label' => 'ホーム', 'url' => '/dashboard'],
                ['label' => '会員情報', 'url' => "/users/{$request->user()->id}"],
                ['label' => 'ログアウト', 'action' => 'logout'],
            ],
        ];
    }
}
