<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\ResisterMedicalStaffRequest;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService) {
        $result = $authService->attemptLogin($request->validated());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 401);
        }

        //メール認証チェック
        if (!$result['user']->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'メール認証が完了していません',
            ], 403);
        }

        return response()->json([
            'message' => 'ログインに成功しました',
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

    public function registerMedicalStaff(ResisterMedicalStaffRequest $request) {
        $validated = $request->validated();

        $validated['role_id'] = Role::where('name', 'medical_staff')->first()->id;
        $validated['password'] = Hash::make($validated['password']);

        //承認待ち
        $validated['approved_at'] = null;
        $validated['approved_by'] = null;
        $validated['status'] = UserStatus::Pending;
        $validated['email_verified_at'] = null;

        $user = User::create($validated);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => '登録が完了しました。承認をお待ちください。',
            'user' => $user,
        ], 201);
    }

    public function medicalInstitutions() {
        // id と name だけを取得して返却
        $institutions = MedicalInstitution::select('id', 'name')->get();

        return response()->json([
            'data' => $institutions
        ]);
    }
}
