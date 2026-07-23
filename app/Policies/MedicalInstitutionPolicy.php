<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicalInstitution;

class MedicalInstitutionPolicy
{
    protected function isSameInstitution(User $user, MedicalInstitution $institution): bool
    {
        return $user->medical_institution_id !== null
            && $user->medical_institution_id === $institution->id;
    }

    public function view(User $user, MedicalInstitution $institution):bool {
        $roleName = optional($user->role)->name;

        //staffは全医療機関閲覧可
        if ($roleName === 'staff') {
            return true;
        }

        //directorとmemberは自分の医療機関のみ閲覧可
        if (in_array($roleName, ['member', 'director'], true)) {
            return $this->isSameInstitution($user, $institution);
        }

        //medical_staffは閲覧不可
        return false;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, MedicalInstitution $institution)
    {
        // member / director は自分の医療機関のみ編集可
        if (in_array( optional($user->role)->name, ['member', 'director'], true)) {
            return $this->isSameInstitution($user, $institution);
        }

        // staff、medical_staff 不可
        return false;
    }

    public function delete(User $user, MedicalInstitution $institution)
    {
        return false;
    }
}