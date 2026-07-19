<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait CalendarByMonth
{
    public function buildMonthlyCalendar(Collection $occurrences, string $month)
    {
        // month = "2026-05"
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        // 月初と月末
        $start = Carbon::create($year, $monthNum, 1);
        $end = $start->copy()->endOfMonth();

        // 月の日付一覧を作成
        $calendar = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $calendar[$key] = []; // 初期化
        }

        // occurrences を日付ごとに入れる
        foreach ($occurrences as $occurrence) {
            $day = $occurrence->start_at->format('Y-m-d');

            if (isset($calendar[$day])) {
                $calendar[$day][] = $occurrence;
            }
        }

        return [
            $month => $calendar
        ];
    }
}
