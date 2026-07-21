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

        $memberId = Role::where('name', 'member')->value('id');
        $directorId = Role::where('name', 'director')->value('id');
        $medicalStaffId = Role::where('name', 'medical_staff')->value('id');

        User::whereIn('role_id', [$memberId, $directorId, $medicalStaffId])
            ->get()
            ->each(function ($user) use ($institutions) {
                if ($user->medical_institution_id === null) {
                    $user->medical_institution_id = $institutions->random()->id;
                    $user->save();
                }
            });
    }
}
