<?php

namespace App\Http\Controllers;

use App\Models\ScheduleOccurrence;
use App\Traits\FiltersByPolicy;
use App\Traits\CalendarByMonth;
use App\Traits\CalendarLinks;

class ScheduleController extends Controller
{
    use FiltersByPolicy, CalendarByMonth, CalendarLinks;

    public function index() {
        $month = request()->query('month', now()->format('Y-m'));

        $occurrences = ScheduleOccurrence::with([
            'schedule.room',
            'schedule.category'])
            ->whereYear('start_at', substr($month, 0, 4))
            ->whereMonth('start_at', substr($month, 5, 2))
            ->orderBy('start_at')
            ->get();

        $filtered = $this->filterByPolicy($occurrences);

        return [
            'calendar' => $this->buildMonthlyCalendar($filtered, $month),
            'month_links' => $this->buildMonthLinks($month),
            'year_links' => $this->buildYearLinks(substr($month, 0, 4)),
        ];
    }
}
