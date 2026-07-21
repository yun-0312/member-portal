<?php

namespace Database\Factories;

use App\Models\Video;
use App\Models\Workshop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            '地域医療連携研修会',
            '在宅医療研修会',
            '感染症対策講習会',
            '災害時医療対応研修会',
            '高齢者医療と介護連携セミナー',
        ];

        $urls = [
            'https://www.youtube.com/watch?v=Ke6XX8FHOHM',
            'https://www.youtube.com/watch?v=zsJpUCWfyPE',
            'https://www.youtube.com/watch?v=d0yGdNEWdn0',
            'https://www.youtube.com/watch?v=w-HYZv6HzAs',
            'https://www.youtube.com/watch?v=8S0FDjFBj8o',
        ];

        $publishedAt = now()->subDays(fake()->numberBetween(1, 30));
        $expiredAt   = $publishedAt->copy()->addDays(fake()->numberBetween(30, 120));

        return [
            'title' => $this->faker->randomElement($titles),
            'description' => $this->faker->realText(fake()->numberBetween(25, 50)),
            'external_url' => $this->faker->randomElement($urls),
            'published_at' => $publishedAt,
            'expired_at' => $expiredAt,
            'created_by' => User::whereHas('role', fn($q) => $q->where('name', 'staff'))
                ->inRandomOrder()
                ->value('id'),
        ];
    }
}
