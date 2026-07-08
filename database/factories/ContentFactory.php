<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ContentCategory;
use App\Models\User;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => ContentCategory::inRandomOrder()->first()->id,
            'group_id'    => null,
            'title'       => $this->faker->sentence(4),
            'body'        => $this->faker->realText(fake()->numberBetween(100, 150)),
            'meeting_date' => null,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_by'   => User::inRandomOrder()->first()->id,
        ];
    }
}
