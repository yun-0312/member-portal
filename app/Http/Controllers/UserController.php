<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Notifications\UserApprovedNotification;

class UserController extends Controller
{
    public function show(User $user) {
        $this->authorize('view', $user);

        $authUser = auth()->user();

        $userArray = $user->load(['role', 'medicalInstitution'])->toArray();
        unset($userArray['approved_by']);

        $retireUrl = null;
        $retiredMessage = null;
        $usersUrl = route('medical-institutions.users', $user->medical_institution_id);

        $authRoleName = optional($authUser->role)->name;
        $targetRoleName = optional($user->role)->name;

        $isRetired = ($user->status === UserStatus::Retired || $user->status === UserStatus::Retired->value);

        if ($isRetired) {
            $retiredMessage = 'このスタッフは退職済みです';
        }

        if (
            in_array($authRoleName, ['director', 'member'], true) &&
            $authUser->medical_institution_id !== null &&
            $authUser->medical_institution_id === $user->medical_institution_id
        ) {
            // 退職済みかどうか
            if ($targetRoleName === 'medical_staff') {
                if (!$isRetired) {
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



    public function approve(User $user) {
        $this->authorize('approve', $user);

        $user->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'status' => UserStatus::Active,
        ]);

         // 承認完了メールを送る
        $user->notify(new UserApprovedNotification());

        return response()->json([
            'message' => 'ユーザーを承認しました',
            'user' => $user,
        ]);
    }

    public function reject(User $user) {
        $this->authorize('reject', $user);

        if ($user->status === UserStatus::Active) {
            return response()->json([
                'message' => '承認済みユーザーは却下できません',
            ], 422);
        }

        $user->update([
            'approved_at' => null,
            'approved_by' => null,
            'status' => UserStatus::Rejected,
        ]);

        return response()->json([
            'message' => 'ユーザーを却下しました',
            'user' => $user,
        ]);
    }

    public function retire(User $user) {
        $this->authorize('retire', $user);

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
        $this->authorize('update', $request->user());

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
