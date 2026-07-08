<?php

namespace Database\Factories;

use App\Models\MedicalInstitution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalInstitution>
 */
class MedicalInstitutionFactory extends Factory
{
    protected $model = MedicalInstitution::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hospitalTypes = ['医院', 'クリニック', '診療所', '内科', '耳鼻咽喉科', '眼科', '整形外科', '消化器内科'];
        $randomDate = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'name' => $this->faker->lastName() . $this->faker->randomElement($hospitalTypes),
            'address' => $this->faker->address(),
            'phone' => $this->faker->numerify('03-####-####'),
            'created_at' => $randomDate,
            'updated_at' => $randomDate,
        ];
    }
}
