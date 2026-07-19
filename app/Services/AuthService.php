<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function attemptLogin(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'メールアドレスまたはパスワードか違います。',
            ];
        }

        $user = Auth::user();

        $token = $user->createToken('api')->plainTextToken;

        return [
            'success' => true,
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function me()
    {
        $user = auth()->user();

        if (is_null($user->approved_at) || is_null($user->approved_by)) {
            return [
                'status' => 'pending',
                'message' => '承認をお待ちください。',
                'user' => $user,
            ];
        }

        return [
            'status' => 'active',
            'user' => $user->load(['role', 'medicalInstitution']),
        ];
    }
}

