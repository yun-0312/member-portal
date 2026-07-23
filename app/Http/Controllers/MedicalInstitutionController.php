<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\MedicalInstitution;

class MedicalInstitutionController extends Controller
{
    public function show(MedicalInstitution $medicalInstitution)
    {
        $this->authorize('view', $medicalInstitution);

        return response()->json([
            'institution' => $medicalInstitution,
            'edit_url' => route('admin.medical-institutions.update', ['medicalInstitution' => $medicalInstitution->id]),
            'users_url' => route('medical-institutions.users', ['medicalInstitution' => $medicalInstitution->id]),
        ]);
    }

    public function users(MedicalInstitution $medicalInstitution) {
        $this->authorize('view', $medicalInstitution);

        $users = $medicalInstitution->users()
            ->whereNotIn('status', [
                UserStatus::Retired,
                UserStatus::Rejected,
                UserStatus::Retired->value,
                UserStatus::Rejected->value,
            ])
            ->with('role')
            ->orderBy('created_at', 'desc')
            ->get();

        $users->transform(function ($user) {
            $user->is_pending = ($user->status === UserStatus::Pending || $user->status === UserStatus::Pending->value);

            if ($user->is_pending) {
                $user->approve_url = route('users.approve', ['user' => $user->id]);
                $user->reject_url = route('users.reject', ['user' => $user->id]);
            }

            $user->show_url = route('users.show', ['user' => $user->id]);
            return $user;
        });

        return response()->json([
            'data' => $users,
        ]);
    }
}
