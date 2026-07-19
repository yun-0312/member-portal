<?php

namespace App\Traits;

use Carbon\Carbon;

trait CalendarLinks
{
    public function buildMonthLinks(string $month)
    {
        // month = "2026-05"
        $current = Carbon::createFromFormat('Y-m', $month);

        return [
            'current' => $current->format('Y-m'),
            'prev'    => $current->copy()->subMonth()->format('Y-m'),
            'next'    => $current->copy()->addMonth()->format('Y-m'),
        ];
    }

    public function buildYearLinks(string $year)
    {
        $links = [];

        for ($m = 1; $m <= 12; $m++) {
            $links[] = sprintf('%s-%02d', $year, $m);
        }

        return $links;
    }
}
