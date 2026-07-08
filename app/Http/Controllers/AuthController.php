<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService) {
        $result = $authService->attemptLogin($request->validated());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 401);
        }

        return response()->json([
            'message' => 'ログイン成功',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function logout(Request $request, AuthService $authService) {
        $authService->logout($request->user());

        return response()->json([
            'message' => 'ログアウトしました',
        ]);
    }

    public function me(AuthService $authService) {
        return response()->json([
            'user' => $authService->me(),
        ]);
    }
}
