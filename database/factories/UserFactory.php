<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => Role::where('name', 'member')->value('id'),
            'status' => 1,
            'approved_at' => now(),
            'approved_by' => null,
            'medical_institution_id' => null,
            'remember_token' => Str::random(10),
        ];
    }

    //admin作成
    public function admin() {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'admin')->value('id'),
            'medical_institution_id' => null,
        ]);
    }

    //staff作成
    public function staff() {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'staff')->value('id'),
            'medical_institution_id' => null,
        ]);
    }

    //director作成
    public function director() {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'director')->value('id'),
        ]);
    }

    //member作成
    public function member() {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'member')->value('id'),
        ]);
    }

    //medicalStaff作成
    public function medicalStaff() {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'medical_staff')->value('id'),
            'medical_institution_id' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
