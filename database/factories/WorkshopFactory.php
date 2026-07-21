<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //タイトル
        $titles = [
            '保険委員会講習会',
            '介護保険委員会研修会',
            '予防接種説明会',
            '公衆衛生委員会講習会',
            '事業説明会',
            'がん検診研修会',
            '地域医療研修会',
            '在宅医療研修会',
            '学術研修会',
            '胃がん検診研修会',
            '肺がん検診研修会',
            '認知症研修会',
        ];

        //研修会の開始時間
        $start = $this->faker->dateTimeBetween('now', '+2 month');

        //研修会の終了時間
        $end = (clone $start)->modify('+' . rand(1, 2) . ' hours');

        //会議室
        $rooms = [
            '医師会館　4階講堂',
            'Zoom　Web上',
            '医師会館　4階講堂（ハイブリッド開催）'
        ];

        //医療機関名
        $hospitalNames = [
            'さくら医院',
            'ひまわりクリニック',
            'みどり町診療所',
            '東中央病院',
            'しらゆき病院',
            '南市立病院',
            '西上病院',
            '北下病院',
        ];

        return [
            'title' => $this->faker->randomElement($titles),
            'description' =>$this->faker->realText(50),
            'start_at' => $start,
            'end_at' => $end,
            'location' => $this->faker->randomElement($rooms),
            'lecture' => $this->faker->randomElement($hospitalNames) . $this->faker->name(),
            'created_by' => User::whereHas('role', fn($q) => $q->where('name', 'staff'))
                ->inRandomOrder()
                ->value('id'),
        ];
    }
}
