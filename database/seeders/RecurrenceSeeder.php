<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\ScheduleCategory;
use App\Models\ScheduleRecurrence;

class RecurrenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //委員会カテゴリID
        $committeeCat = ScheduleCategory::where('slug', 'committee')->value('id');

        //委員会スケジュール一覧
        $committeeSchedules = Schedule::where('schedule_category_id', $committeeCat)->get();

        // 曜日候補
        $weekdays = ['MO', 'TU', 'WE', 'TH', 'FR'];

        foreach ($committeeSchedules as $schedule) {
            $byweekday = $weekdays[array_rand($weekdays)];
            $bysetpos = rand(1, 4);

            ScheduleRecurrence::create([
                'schedule_id' => $schedule->id,
                'frequency'   => 'monthly',
                'byweekday'   => [$byweekday],
                'bysetpos'    => $bysetpos,
                'interval'    => 1,
                'until'       => null,
                'start_after' => null,
            ]);
        }

        // 理事会（第2・第4金曜）
        $importantCat = ScheduleCategory::where('slug', 'important')->value('id');
        $boardSchedules = Schedule::where('schedule_category_id', $importantCat)->get();

        foreach ($boardSchedules as $schedule) {
            // 第2金曜
            ScheduleRecurrence::create([
                'schedule_id' => $schedule->id,
                'frequency'   => 'monthly',
                'byweekday'   => ['FR'],
                'bysetpos'    => 2,
                'interval'    => 1,
            ]);

            // 第4金曜
            ScheduleRecurrence::create([
                'schedule_id' => $schedule->id,
                'frequency'   => 'monthly',
                'byweekday'   => ['FR'],
                'bysetpos'    => 4,
                'interval'    => 1,
            ]);
        }
    }
}
