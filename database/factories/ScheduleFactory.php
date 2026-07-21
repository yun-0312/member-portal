<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Room;
use App\Models\User;
use App\Models\ScheduleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::inRandomOrder()->value('id'),
            'title' => $this->faker->sentence(3),
            'schedule_category_id' => ScheduleCategory::inRandomOrder()->value('id'),
            'location' => null,
            'url' => null,
            'created_by' => User::whereHas('role', fn($q) =>
                $q->where('name', 'staff')
            )->inRandomOrder()->value('id'),
        ];
    }
}
