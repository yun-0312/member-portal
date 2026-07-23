<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $target): bool {

        $roleName = optional($user->role)->name;

        //staffは全員閲覧可
        if ($roleName === 'staff') {
            return true;
        }

        //member、directorは同じ医療機関の会員のみ閲覧可
        if (in_array($roleName, ['member', 'director'], true)) {
            return $user->medical_institution_id !== null
                && $user->medical_institution_id === $target->medical_institution_id;
        }

        //medical_staffは自分の情報のみ閲覧可
        if ($roleName === 'medical_staff') {
            return $user->id === $target->id;
        }

        return false;
    }

    public function create(User $user): bool {
        return false;
    }

    public function update(User $user, User $target): bool {
        //自分のプロフィール変更のみ可
        return $user->id === $target->id;
    }

    public function delete(User $user, User $target): bool {
        return false;
    }

     //スタッフの退職処理 (retire)
    public function retire(User $user, User $target): bool
    {
        // 自分自身は退職不可
        if ($user->id === $target->id) {
            return false;
        }

        // 対象が medical_staff でない場合は不可
        if (optional($target->role)->name !== 'medical_staff') {
            return false;
        }

        $roleName = optional($user->role)->name;

        // 実行者が director または member の場合
        if (in_array($roleName, ['director', 'member'], true)) {
            // 同じ医療機関に所属している場合のみ許可
            return $user->medical_institution_id !== null
                && $user->medical_institution_id === $target->medical_institution_id;
        }

        return false;
    }

     //登録承認・却下 (approve / reject)
    public function approve(User $user, User $target): bool
    {
        return $this->canManageInstitutionStaff($user, $target);
    }

    public function reject(User $user, User $target): bool
    {
        return $this->canManageInstitutionStaff($user, $target);
    }

     //医療機関内スタッフ管理権限の共通判定（ヘルパー関数）
    private function canManageInstitutionStaff(User $user, User $target): bool
    {
        $roleName = optional($user->role)->name;

        // 実行者が director または member
        if (in_array($roleName, ['director', 'member'], true)) {
            // 同じ医療機関に所属するユーザーのみ承認・却下可能
            return $user->medical_institution_id !== null
                && $user->medical_institution_id === $target->medical_institution_id;
        }

        return false;
    }

}