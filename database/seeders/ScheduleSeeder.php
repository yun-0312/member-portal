<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Room;
use App\Models\ScheduleCategory;
use App\Models\workshop;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::pluck('id')->toArray();

        //カテゴリID
        $committeeCat = ScheduleCategory::where('slug', 'committee')->value('id');
        $importantCat = ScheduleCategory::where('slug', 'important')->value('id');
        $lectureCat = ScheduleCategory::where('slug', 'lecture')->value('id');

        // 半年前〜1年後まで
        $start = Carbon::now()->subMonths(6)->startOfMonth();
        $end   = Carbon::now()->addYear()->endOfMonth();

        $current = $start->clone();

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

        $boardCount = 1; // 理事会の通し番号

        while ($current->lte($end)) {
            $year = $current->year;
            $month = $current->month;
            $isAugust = ($month === 8);

            // 委員会（第1〜4週 水曜・金曜）
            if (!$isAugust) {
                foreach ($groups as $group) {
                    $date = Carbon::parse("first wednesday of $year-$month")
                        ->setTime(20, 0);

                    Schedule::create([
                        'title' => $group,
                        'schedule_category_id' => $committeeCat,
                        'room_id' => $committeeRooms[array_rand($committeeRooms)],
                        'location' => null,
                        'url' => null,
                        'start_at' => $date,
                        'end_at' => $date->clone()->addHour(),
                        'created_by' => 1,
                    ]);
                }
            }

            // 理事会（第2・4週 金曜）
            foreach ([2, 4] as $week) {

                $date = Carbon::parse("first friday of $year-$month")
                    ->addWeeks($week - 1)
                    ->setTime(20, 0);

                Schedule::create([
                    'title' => '第' . $boardCount . '回理事会',
                    'schedule_category_id' => $importantCat,
                    'room_id' => $boardRoom,
                    'location' => null,
                    'url' => null,
                    'start_at' => $date,
                    'end_at' => $date->clone()->addHours(1),
                    'created_by' => 1,
                ]);
                $boardCount++;
            }

            // 研修会（月3〜4件）
            $workshops = Workshop::inRandomOrder()
                ->take(rand(3, 4))
                ->get();

            foreach ($workshops as $ws) {

                $day = rand(1, $current->daysInMonth);
                $date = Carbon::create($year, $month, $day, 20, 0);

                Schedule::create([
                    'title' => "{$ws->title}（研修会）",
                    'schedule_category_id' => $lectureCat,
                    'room_id' => $rooms[array_rand($rooms)],
                    'location' => null,
                    'url' => "https://example.com/workshop/{$ws->id}",
                    'start_at' => $date,
                    'end_at' => $date->clone()->addHour(),
                    'created_by' => 1,
                ]);
            }

        $current->addMonth();
        }
    }
}
