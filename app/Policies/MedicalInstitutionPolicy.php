<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicalInstitution;
use App\Policies\BasePolicy;

class MedicalInstitutionPolicy extends BasePolicy
{
    public function view(User $user, MedicalInstitution $institution)
    {
        //admin、staffは全件閲覧可
        if (in_array($user->role->name, ['admin', 'staff'])) {
            return true;
        }

        //directorとmemberは自分の医療機関のみ閲覧可
        if (in_array($user->role->name, ['member', 'director'])) {
            return $user->medical_institution_id === $institution->id;
        }

        //medical_staffは閲覧不可
        return false;
    }

        public function update(User $user, MedicalInstitution $institution)
    {
        // admin / staff は全件編集可
        if (in_array($user->role->name, ['admin', 'staff'])) {
            return true;
        }

        // member / director は自分の医療機関のみ編集可
        if (in_array($user->role->name, ['member', 'director'])) {
            return $user->medical_institution_id === $institution->id;
        }

        // medical_staff は編集不可
        return false;
    }

        public function delete(User $user, MedicalInstitution $institution)
    {
        // admin / staff のみ削除可
        return in_array($user->role->name, ['admin', 'staff']);
    }
}