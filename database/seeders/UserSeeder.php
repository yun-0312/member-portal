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
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => '1',
            'status' => 1,
            'approved_at' => now(),
            'approved_by' => null,
            'medical_institution_id' => null,
            'remember_token' => Str::random(10),
        ]);

        User::factory()->admin()->count(1)->create();
        User::factory()->staff()->count(10)->create();
        User::factory()->director()->count(10)->create();
        User::factory()->member()->count(50)->create();
        User::factory()->medicalStaff()->count(30)->create();
    }
}
