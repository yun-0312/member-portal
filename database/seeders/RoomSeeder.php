<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['name' => '4階講堂', 'sort_order' => 1],
            ['name' => '5階第1会議室', 'sort_order' => 2],
            ['name' => '5階第2会議室', 'sort_order' => 3],
            ['name' => '6階第3会議室', 'sort_order' => 4],
            ['name' => '7階日本間', 'sort_order' => 5],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
