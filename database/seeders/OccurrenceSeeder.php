<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\ScheduleRecurrence;
use App\Models\ScheduleOccurrence;
use Carbon\Carbon;

class OccurrenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 半年前〜1年後まで生成
        $start = Carbon::now()->subMonths(6)->startOfMonth();
        $end   = Carbon::now()->addYear()->endOfMonth();

        // 1. recurrence がある schedule の occurrence を生成
        $recurrences = ScheduleRecurrence::with('schedule')->get();

        foreach ($recurrences as $rec) {

            $current = $start->clone();

            while ($current->lte($end)) {

                // 月ごとに処理
                $year  = $current->year;
                $month = $current->month;

                // recurrence のルールに従って日付を計算
                $date = $this->calculateDateFromRecurrence($rec, $year, $month);

                if (!$date) {
                    $current->addMonth();
                    continue;
                }

                // 部屋の空き状況チェック
                if ($this->isRoomAvailable($rec->schedule->room_id, $date, $date->clone()->addHour())) {

                    ScheduleOccurrence::create([
                        'schedule_id'  => $rec->schedule_id,
                        'recurrence_id'=> $rec->id,
                        'start_at'     => $date,
                        'end_at'       => $date->clone()->addHour(),
                        'type'         => 'generated',
                    ]);
                }

                $current->addMonth();
            }
        }

        // 2. recurrence がない schedule（研修会）の occurrence を生成
        $singleSchedules = Schedule::doesntHave('recurrences')->get();

        foreach ($singleSchedules as $schedule) {

            // 月2〜3回
            for ($i = 0; $i < rand(2, 3); $i++) {

                $date = Carbon::now()
                    ->addMonths(rand(0, 12))
                    ->startOfMonth()
                    ->addDays(rand(0, 27))
                    ->setTime(20, 0);

                if ($this->isRoomAvailable($schedule->room_id, $date, $date->clone()->addHour())) {

                    ScheduleOccurrence::create([
                        'schedule_id'  => $schedule->id,
                        'recurrence_id'=> null,
                        'start_at'     => $date,
                        'end_at'       => $date->clone()->addHour(),
                        'type'         => 'generated',
                    ]);
                }
            }
    }
}

    //recurrence のルールから日付を計算する
    private function calculateDateFromRecurrence($rec, $year, $month)
    {
        if (empty($rec->byweekday) || !isset($rec->byweekday[0])) {
            return null;
        }

        $weekday = $rec->byweekday[0];
        $weekpos = $rec->bysetpos;

        $date = Carbon::parse("first {$this->weekdayToCarbon($weekday)} of {$year}-{$month}")
            ->addWeeks($weekpos - 1)
            ->setTime(20, 0);

        // until / start_after の範囲チェック
        if ($rec->start_after && $date->lt(Carbon::parse($rec->start_after))) {
            return null;
        }
        if ($rec->until && $date->gt(Carbon::parse($rec->until))) {
            return null;
        }

        return $date;
    }

    //Carbon の曜日名に変換
    private function weekdayToCarbon($weekday)
    {
        $map = [
            'MO' => 'monday',
            'TU' => 'tuesday',
            'WE' => 'wednesday',
            'TH' => 'thursday',
            'FR' => 'friday',
            'SA' => 'saturday',
            'SU' => 'sunday',
        ];
        return $map[$weekday] ?? null;
    }

     //部屋の空き状況チェック
    private function isRoomAvailable($roomId, Carbon $start, Carbon $end): bool
    {
        return !ScheduleOccurrence::whereHas('schedule', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            })
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end]);
            })
            ->exists();
    }

}