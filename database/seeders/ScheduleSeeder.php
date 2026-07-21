<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Room;
use App\Models\ScheduleCategory;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //カテゴリID
        $committeeCat = ScheduleCategory::where('slug', 'committee')->value('id');
        $importantCat = ScheduleCategory::where('slug', 'important')->value('id');
        $lectureCat = ScheduleCategory::where('slug', 'lecture')->value('id');

        //委員会開催場所
        $committeeRooms = Room::whereIn('name', [
            '4階講堂',
            '5階第1会議室',
            '5階第2会議室',
            '6階第3会議室',
        ])->pluck('id')->toArray();

        //理事会開催場所
        $boardRoom = Room::where('name', '5階第1会議室')->value('id');

        //委員会
        $groups = [
            '総務庶務委員会',
            '経理委員会',
            '保健事業委員会',
            '公衆衛生・学校医委員会',
            '地域医療連携委員会',
            '在宅医療委員会',
            '病院・防災・救急委員会',
            '学術・臨床研修委員会',
            '保険委員会',
            '広報・医療情報委員会',
        ];

        // 委員会（毎月1回）
        foreach ($groups as $group) {
            Schedule::create([
                'title' => $group,
                'schedule_category_id' => $committeeCat,
                'room_id' => $committeeRooms[array_rand($committeeRooms)],
                'location' => null,
                'url' => null,
                'created_by' => 1,
            ]);
        }

        // 理事会（毎月2回）
        for ($i = 1; $i <= 24; $i++) {
            Schedule::create([
                'title' => "第{$i}回理事会",
                'schedule_category_id' => $importantCat,
                'room_id' => Room::where('name', '5階第1会議室')->value('id'),
                'location' => null,
                'url' => null,
                'created_by' => 1,
            ]);
        }

        // 研修会（月2〜3件）
        for ($month = 1; $month <= 12; $month++) {
            $count = rand(2, 3);

            for ($i = 0; $i < $count; $i++) {
                Schedule::create([
                    'title' => $groups[array_rand($groups)] . "研修会",
                    'schedule_category_id' => $lectureCat,
                    'room_id' => Room::where('name', '4階講堂')->value('id'),
                    'location' => null,
                    'url' => null,
                    'created_by' => 1,
                ]);
            }
        }
    }
}