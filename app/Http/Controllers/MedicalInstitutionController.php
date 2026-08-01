<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\MedicalInstitution;

class MedicalInstitutionController extends Controller
{
    public function show(MedicalInstitution $medicalInstitution)
    {
        $this->authorize('view', $medicalInstitution);

        $medicalInstitution->load('representative');

        return response()->json([
            'institution' => $medicalInstitution,
            'edit_url' => "/medical-institutions/{$medicalInstitution->id}/edit",
            'users_url' => "/medical-institutions/{$medicalInstitution->id}/users",
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
            ->with('role', 'medicalInstitution')
            ->orderBy('created_at', 'desc')
            ->get();

        $users->transform(function ($user) {
            $user->is_pending = ($user->status === UserStatus::Pending || $user->status === UserStatus::Pending->value);

            if ($user->is_pending) {
                $user->approve_url = "/users/{$user->id}/approve";
                $user->reject_url = "users/{$user->id}/reject";
            }

            $user->show_url = "/users/{$user->id}";

            return $user;
        });

        return response()->json([
            'data' => $users,
        ]);
    }
}
