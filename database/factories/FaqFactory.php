<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomDate = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'question' => $this->faker->realText(fake()->numberBetween(25, 50)),
            'answer' => $this->faker->realText(fake()->numberBetween(25, 50)),
            'category_id' => FaqCategory::inRandomOrder()->value('id'),
            'created_by' => User::WhereHas('role', fn($q) =>
                $q->where('name', 'staff'))->inRandomOrder()->value('id'),
            'created_at' => $randomDate,
            'updated_at' => $randomDate,
        ];
    }
}
