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

        $staffUsers = User::whereHas('role', fn($q) => $q->where('name', 'staff'))->get();

        return [
            'title' => $this->faker->sentence(3),
            'description' =>$this->faker->paragraph(),
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
