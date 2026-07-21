<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show(User $user) {
        $this->authorize('view', $user);

        $authUser = auth()->user();

        $userArray = $user->load(['role', 'medicalInstitution'])->toArray();
        unset($userArray['approved_by']);

        $retireUrl = null;
        $retiredMessage = null;
        $usersUrl = null;

        if (
            in_array($authUser->role->name, ['director', 'member']) &&
            $authUser->medical_institution_id === $user->medical_institution_id
        ) {
            $usersUrl = route('medical-institutions.users', $authUser->medical_institution_id);

            // 退職済みかどうか
            if ($user->role->name === 'medical_staff') {
                if ($user->status === UserStatus::Retired->value) {
                    $retiredMessage = 'このスタッフは退職済みです';
                } else {
                    $retireUrl = route('users.retire', $user->id);
                }
            }
        }

        return response()->json([
            'user' => $userArray,
            'retire_url' => $retireUrl,
            'retired_message' => $retiredMessage,
            'users_url' => $usersUrl,
        ]);
    }

    public function retire(User $user) {
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => '自分を退職扱いにすることはできません',
            ], 422);
        }

        if ($user->role->name != 'medical_staff') {
            return response()->json([
                'message' => '医療機関スタッフ以外は退職扱いにできません',
            ], 422);
        }

        $user->update([
            'status' => UserStatus::Retired,
        ]);

        return response()->json([
            'message' => 'スタッフを退職扱いにしました',
            'user' => $user,
        ]);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user(); // ログイン済みのユーザー

        // 現在のパスワードが正しいかチェック
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => '現在のパスワードが正しくありません。',
            ], 400);
        }

        // 新しいパスワードに更新
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'パスワードを変更しました。',
        ], 200);
    }


}
