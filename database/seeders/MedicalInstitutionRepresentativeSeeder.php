<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicalInstitution;
use App\Models\Role;
use App\Models\User;

class MedicalInstitutionRepresentativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MedicalInstitution::all()->each(function ($institution) {

            $representative = User::where('medical_institution_id', $institution->id)
                ->whereIn('role_id', [
                    Role::where('name', 'director')->value('id'),
                    Role::where('name', 'member')->value('id'),
                    Role::where('name', 'medical_staff')->value('id'),
                ])
                ->inRandomOrder()
                ->first();

            if ($representative) {
                $institution->representative_user_id = $representative->id;
                $institution->save();
            }
        });
    }
}
