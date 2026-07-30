<?php

namespace App\Http\Controllers\Admin\Layout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HeaderController extends Controller
{
    public function index(Request $request) {

        $user = $request->user();

        return response()->json([
            'user' => [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => $user->role?->name ?? 'admin',
            ],
            'menu' => [
                ['label' => '管理画面', 'url' => '/admin/management'],
                ['label' => 'ホーム', 'url' => '/admin/dashboard'],
                ['label' => 'ログアウト', 'action' => 'logout'],
            ],
        ]);

    }
}
