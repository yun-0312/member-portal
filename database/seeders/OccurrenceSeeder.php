<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\ScheduleRecurrence;
use App\Models\ScheduleOccurrence;
use Carbon\Carbon;

class OccurrenceSeeder extends Seeder
{
    public function run(): void
    {
        // タイムゾーンを明示的に指定
        $tz = 'Asia/Tokyo';

        // 半年前〜1年後まで生成
        $start = Carbon::now($tz)->subMonths(6)->startOfMonth();
        $end   = Carbon::now($tz)->addYear()->endOfMonth();

        // recurrence がある schedule の occurrence を生成
        $recurrences = ScheduleRecurrence::with('schedule')->get();

        foreach ($recurrences as $rec) {

            $current = $start->clone();

            while ($current->lte($end)) {

                $year  = $current->year;
                $month = $current->month;

                $date = $this->calculateDateFromRecurrence($rec, $year, $month);

                if (!$date) {
                    $current->addMonth();
                    continue;
                }

                // 部屋の空き状況チェック
                if ($this::isRoomAvailable($rec->schedule->room_id, $date, $date->clone()->addHour())) {

                    ScheduleOccurrence::create([
                        'schedule_id'  => $rec->schedule_id,
                        'recurrence_id'=> $rec->id,
                        'start_at'     => $date->format('Y-m-d H:i:s'), // ★文字列としてフォーマット渡しすると確実
                        'end_at'       => $date->clone()->addHour()->format('Y-m-d H:i:s'),
                        'type'         => 'generated',
                    ]);
                }

                $current->addMonth();
            }
        }

        // recurrence がない schedule（研修会）の occurrence を生成
        $singleSchedules = Schedule::doesntHave('recurrences')->get();

        foreach ($singleSchedules as $schedule) {

            for ($i = 0; $i < rand(2, 3); $i++) {

                // タイムゾーン指定付きで 20:00 を作成
                $date = Carbon::create(
                    Carbon::now($tz)->year,
                    Carbon::now($tz)->month,
                    1,
                    20,
                    0,
                    0,
                    $tz
                )
                ->addMonths(rand(0, 12))
                ->addDays(rand(0, 27));

                if ($this::isRoomAvailable($schedule->room_id, $date, $date->clone()->addHour())) {

                    ScheduleOccurrence::create([
                        'schedule_id'  => $schedule->id,
                        'recurrence_id'=> null,
                        'start_at'     => $date->format('Y-m-d H:i:s'), // ★文字列渡し
                        'end_at'       => $date->clone()->addHour()->format('Y-m-d H:i:s'),
                        'type'         => 'generated',
                    ]);
                }
            }
        }
    }

    private function calculateDateFromRecurrence($rec, $year, $month)
    {
        if (empty($rec->byweekday) || !isset($rec->byweekday[0])) {
            return null;
        }

        $tz = 'Asia/Tokyo';
        $weekday = $rec->byweekday[0];   // MO, TU, ...
        $weekpos = $rec->bysetpos;       // 1, 2, 3, 4, 5

        $base = Carbon::create($year, $month, 1, 20, 0, 0, $tz);

        $date = null;
        $count = 0;

        for ($day = 1; $day <= $base->daysInMonth; $day++) {

            $d = Carbon::create($year, $month, $day, 20, 0, 0, $tz);

            if ($d->format('D') === $this->weekdayToCarbonShort($weekday)) {
                $count++;

                if ($count === $weekpos) {
                    $date = $d;
                    break;
                }
            }
        }

        if (!$date) return null;

        // 範囲チェック
        if ($rec->start_after && $date->lt(Carbon::parse($rec->start_after, $tz))) {
            return null;
        }
        if ($rec->until && $date->gt(Carbon::parse($rec->until, $tz))) {
            return null;
        }

        return $date;
    }

    private function weekdayToCarbonShort($weekday)
    {
        return [
            'MO' => 'Mon',
            'TU' => 'Tue',
            'WE' => 'Wed',
            'TH' => 'Thu',
            'FR' => 'Fri',
            'SA' => 'Sat',
            'SU' => 'Sun',
        ][$weekday] ?? null;
    }

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