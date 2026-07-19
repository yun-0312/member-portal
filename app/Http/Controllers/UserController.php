<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserStatus;

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
}
