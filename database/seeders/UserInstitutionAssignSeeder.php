<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MedicalInstitution;
use App\Models\Role;


class UserInstitutionAssignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = MedicalInstitution::all();

        if ($institutions->isEmpty()) {
            return;
        }

        $memberId = Role::where('name', 'member')->value('id');
        $directorId = Role::where('name', 'director')->value('id');
        $medicalStaffId = Role::where('name', 'medical_staff')->value('id');

        $priorityUsers = User::whereIn('role_id', [$directorId, $memberId])
            ->whereNull('medical_institution_id')
            ->get()
            ->shuffle();

        $remainingUsers = collect();

        // 各医療機関に最低1人を確実に割り当てる
        foreach ($institutions as $institution) {
            if ($priorityUsers->isNotEmpty()) {
                $user = $priorityUsers->pop(); // 優先リストから1人取り出して割り当て
                $user->medical_institution_id = $institution->id;
                $user->save();
            }
        }

        // 使われなかった director / member を余りリストに追加
        $remainingUsers = $remainingUsers->merge($priorityUsers);

        $medicalStaffUsers = User::where('role_id', $medicalStaffId)
            ->whereNull('medical_institution_id')
            ->get();
        $remainingUsers = $remainingUsers->merge($medicalStaffUsers);

        foreach ($remainingUsers as $user) {
            $user->medical_institution_id = $institutions->random()->id;
            $user->save();
        }

    }
}
