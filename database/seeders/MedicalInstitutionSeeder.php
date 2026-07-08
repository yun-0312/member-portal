<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalInstitution;

class MedicalInstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MedicalInstitution::factory()->count(60)->create();
    }
}