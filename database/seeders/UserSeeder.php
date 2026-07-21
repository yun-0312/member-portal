<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['admin@example.com', 1, null],
            ['staff@example.com', 2, null],
            ['director@example.com', 3, 1],
            ['member@example.com', 4, 1],
            ['medical@example.com', 5, 1],
        ];

        foreach ($users as [$email, $roleId, $institutionId]) {
            User::create([
                'name' => ucfirst(explode('@', $email)[0]),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => $roleId,
                'status' => 1,
                'approved_at' => now(),
                'approved_by' => 1,
                'medical_institution_id' => $institutionId,
            ]);
        }


        User::factory()->admin()->count(1)->create();
        User::factory()->staff()->count(10)->create();
        User::factory()->director()->count(10)->create();
        User::factory()->member()->count(50)->create();
        User::factory()->medicalStaff()->count(30)->create();
    }
}
