<?php

namespace Database\Factories;

use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<notice>
 */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->realText(10),
            'committee_name' => null,
            'body' => $this->faker->realText(fake()->numberBetween(25, 35)),
            'category_id' => NoticeCategory::inRandomOrder()->value('id'),
            'workshop_id' => null,
            'published_at' => now()->subDays(rand(1, 30)),
            'created_by' => User::where('role', fn($q) => $q->where('name', 'staff'))
                ->inRandomOrder()->value('id'),
        ];
    }
}
