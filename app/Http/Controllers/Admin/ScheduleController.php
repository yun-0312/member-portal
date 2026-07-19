<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ScheduleOccurrence;
use App\Http\Requests\ScheduleStoreRequest;
use App\Http\Requests\ScheduleUpdateRequest;
use App\Http\Requests\OccurrenceUpdateRequest;
use App\Models\ScheduleRecurrence;
use Carbon\Carbon;
use App\Traits\CalendarByMonth;
use App\Traits\CalendarLinks;

class ScheduleController extends Controller
{
    use CalendarByMonth, CalendarLinks;

    public function index() {
        $month = request()->query('month', now()->format('Y-m'));

        $occurrences = ScheduleOccurrence::with([
            'schedule.room',
            'schedule.category',
        ])
        ->whereYear('start_at', substr($month, 0, 4))
        ->whereMonth('start_at', substr($month, 5, 2))
        ->orderBy('start_at')
        ->get();

        $occurrences->transform(function ($occurrence) {
            $occurrence->show_url   = route('admin.schedules.show', $occurrence->schedule_id);
            return $occurrence;
        });

        return [
            'calendar' => $this->buildMonthlyCalendar($occurrences, $month),
            'month_links' => $this->buildMonthLinks($month),
            'year_links' => $this->buildYearLinks(substr($month, 0, 4)),
            'store_url' => route('admin.schedules.store'),
        ];
    }

    public function show(Schedule $schedule) {
        $this->authorize('view', $schedule);
        $schedule->load([
            'room',
            'category',
            'recurrences',
            'occurrences',
        ]);

        $scheduleArray = $schedule->toArray();

        $scheduleArray['update_url'] = route('admin.schedules.update', $schedule->id);
        $scheduleArray['destroy_url'] = route('admin.schedules.destroy', $schedule->id);
        $scheduleArray['occurrences'] = $schedule->occurrences->map(function ($occurrence) {
            return [
                'id' => $occurrence->id,
                'start_at' => $occurrence->start_at,
                'end_at' => $occurrence->end_at,
                'type' => $occurrence->type,
                'update_url' => route('admin.schedule-occurrences.update', $occurrence->id),
                'destroy_url' => route('admin.schedule-occurrences.destroy', $occurrence->id),
            ];
        });

        return response()->json([
            'schedule' => $scheduleArray,
        ]);
    }

    public function store(ScheduleStoreRequest $request)
    {
        $validated = $request->validated();

        $schedule = Schedule::create([
            'room_id'             => $validated['room_id'] ?? null,
            'title'               => $validated['title'],
            'schedule_category_id'=> $validated['schedule_category_id'],
            'location'            => $validated['location'] ?? null,
            'url'                 => $validated['url'] ?? null,
            'created_by'          => $request->user()->id,
        ]);

        $skipped = [];

        // recurrence ありの場合
        if (!empty($validated['recurrence'])) {
            $recurrence = $schedule->recurrences()->create([
                'frequency'   => $validated['recurrence']['frequency'],
                'byweekday'   => $validated['recurrence']['byweekday'] ?? null,
                'bysetpos'    => $validated['recurrence']['bysetpos'] ?? null,
                'interval'    => $validated['recurrence']['interval'] ?? 1,
                'until'       => $validated['recurrence']['until'] ?? null,
                'start_after' => null,
            ]);

            $skipped = $this->generateOccurrencesFromRecurrence($schedule, $recurrence);
        } else {
            // 単発 occurrence 生成
            $start = Carbon::parse($validated['start_at']);
            $end   = Carbon::parse($validated['end_at']);

            if ($this->isRoomTimeConflict($schedule->room_id, $start, $end)) {
                return response()->json([
                    'message' => 'この時間帯は既に予約されています',
                ], 422);
            }

            $schedule->occurrences()->create([
                'start_at' => $start,
                'end_at'   => $end,
                'type'     => 'generated',
            ]);
        }

        return response()->json([
            'message'  => empty($skipped)
                ? 'スケジュールを登録しました'
                : '一部の予定は重複のため登録されませんでした',
            'schedule' => $schedule->load(['recurrences', 'occurrences']),
            'skipped'  => $skipped,
        ]);
    }

    public function updateSchedule(ScheduleUpdateRequest $request, Schedule $schedule) {
        $schedule->update($request->validated());

        return response()->json([
            'message' => 'スケジュールを更新しました',
            'schedule' => $schedule->fresh(),
        ]);
    }

    public function updateOccurrence(OccurrenceUpdateRequest $request, ScheduleOccurrence $occurrence)
    {
        $validated = $request->validated();
        $mode      = $validated['mode'];

        $schedule   = $occurrence->schedule;
        $recurrence = $occurrence->recurrence;

        if (($mode === 'future' || $mode === 'all') && !$recurrence) {
            return response()->json([
                'message' => 'この予定は繰り返し予定ではありません',
            ], 422);
        }

        switch ($mode) {
            case 'single':
                return $this->updateSingle($occurrence, $validated);

            case 'future':
                return $this->updateFuture($occurrence, $recurrence, $validated);

            case 'all':
                return $this->updateAll($schedule, $recurrence, $validated);

            default:
                return response()->json(['message' => '更新方法が不正です'], 422);
        }
    }

    // この予定のみ
    private function updateSingle(ScheduleOccurrence $occurrence, array $validated)
    {
        $occurrence->update([
            'recurrence_id' => null,
            'start_at'      => Carbon::parse($validated['start_at']),
            'end_at'        => Carbon::parse($validated['end_at']),
            'type'          => 'exception',
        ]);

        return response()->json([
            'message'    => 'この予定を更新しました',
            'occurrence' => $occurrence->load(['schedule', 'recurrence']),
        ]);
    }

    // これ以降の予定
    private function updateFuture(ScheduleOccurrence $occurrence, ScheduleRecurrence $recurrence, array $validated) {
        $date = Carbon::parse($occurrence->start_at);

        // 旧 recurrence をこの予定の前日までに制限
        $recurrence->update([
            'until' => $date->copy()->subDay(),
        ]);

        // 新 recurrence 作成
        $newRecurrence = $recurrence->schedule->recurrences()->create([
            'frequency'   => $validated['recurrence']['frequency'],
            'byweekday'   => $validated['recurrence']['byweekday'] ?? null,
            'bysetpos'    => $validated['recurrence']['bysetpos'] ?? null,
            'interval'    => $validated['recurrence']['interval'] ?? 1,
            'start_after' => $date,
            'until'       => $validated['recurrence']['until'] ?? null,
        ]);

        // 以降の occurrence を削除
        ScheduleOccurrence::where('recurrence_id', $recurrence->id)
            ->where('start_at', '>=', $date)
            ->delete();

        // 新 recurrence で occurrence を生成
        $this->generateOccurrencesFromRecurrence($recurrence->schedule, $newRecurrence);

        return response()->json([
            'message' => 'この予定以降を更新しました',
        ]);
    }

    // すべて変更
    private function updateAll(
        Schedule $schedule,
        ScheduleRecurrence $recurrence,
        array $validated
    ) {
        $recurrence->update([
            'frequency'   => $validated['recurrence']['frequency'],
            'byweekday'   => $validated['recurrence']['byweekday'] ?? null,
            'bysetpos'    => $validated['recurrence']['bysetpos'] ?? null,
            'interval'    => $validated['recurrence']['interval'] ?? 1,
            'until'       => $validated['recurrence']['until'] ?? null,
            'start_after' => null,
        ]);

        // 既存 occurrence を全削除
        $schedule->occurrences()->delete();

        // 再生成
        $this->generateOccurrencesFromRecurrence($schedule, $recurrence);

        return response()->json([
            'message' => '全ての予定を更新しました',
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        // recurrence がある場合は削除不可
        if ($schedule->recurrences()->exists()) {
            return response()->json([
                'message' => 'このスケジュールは繰り返し予定のため削除できません。',
            ], 422);
        }

        // occurrence がある場合は削除不可
        if ($schedule->occurrences()->exists()) {
            return response()->json([
                'message' => 'このスケジュールには予定が存在するため削除できません。',
            ], 422);
        }
        $schedule->delete();

        return response()->json([
            'message' => 'スケジュールを削除しました',
        ]);
    }

    public function destroyOccurrence(Request $request, ScheduleOccurrence $occurrence) {
        $mode = $request->input('mode');
        $recurrence = $occurrence->recurrence;
        $schedule = $occurrence->schedule;

        // recurrence がない occurrence に future / all は使えない
        if (($mode === 'future' || $mode === 'all') && !$recurrence) {
            return response()->json([
                'message' => 'この予定は繰り返し予定ではありません',
            ], 422);
        }

        switch ($mode) {

            // この予定だけ削除
            case 'single':
                $occurrence->delete();
                return response()->json([
                    'message' => 'この予定を削除しました'
                ]);

            // これ以降を削除
            case 'future':
                $date = Carbon::parse($occurrence->start_at);

                // recurrence をこの occurrence の前日までに縮める
                $recurrence->update([
                    'until' => $date->copy()->subDay(),
                ]);

                // 以降の occurrence を削除
                ScheduleOccurrence::where('recurrence_id', $recurrence->id)
                    ->where('start_at', '>=', $date)
                    ->delete();

                return response()->json([
                    'message' => 'この予定以降を削除しました'
                ]);

            // すべて削除
            case 'all':
                // recurrence 削除
                $recurrence->delete();

                // 全 occurrence 削除
                $schedule->occurrences()->delete();

                // schedule 削除
                $schedule->delete();

                return response()->json([
                    'message' => 'すべての予定を削除しました'
                ]);

            default:
                return response()->json([
                    'message' => '削除方法が不正です'
                ], 422);
        }
    }

    /**
     * recurrence から occurrence を生成（daily / weekly / monthly / yearly 全対応）
     */
    private function generateOccurrencesFromRecurrence(Schedule $schedule, ScheduleRecurrence $recurrence)
    {
        $skipped = [];

        // 開始位置
        $current = $recurrence->start_after
            ? Carbon::parse($recurrence->start_after)->clone()
            : Carbon::now()->startOfMonth();

        // 終了位置
        $end = $recurrence->until
            ? Carbon::parse($recurrence->until)->endOfDay()
            : Carbon::now()->addYear()->endOfMonth();

        switch ($recurrence->frequency) {

            // 毎日
            case 'daily':
                while ($current->lte($end)) {
                    $start = $current->copy()->setTime(20, 0);
                    $endAt = $start->copy()->addHour();

                    if (!$this->isRoomTimeConflict($schedule->room_id, $start, $endAt)) {
                        $schedule->occurrences()->create([
                            'recurrence_id' => $recurrence->id,
                            'start_at'      => $start,
                            'end_at'        => $endAt,
                            'type'          => 'generated',
                        ]);
                    } else {
                        $skipped[] = $start->toDateTimeString();
                    }

                    $current->addDays($recurrence->interval);
                }
                break;

            //  毎週
            case 'weekly':
                $weekdays = $recurrence->byweekday ?? [];

                while ($current->lte($end)) {
                    foreach ($weekdays as $weekday) {
                        $carbonWeekday = $this->weekdayToCarbon($weekday);

                        $start = $current->copy()->next($carbonWeekday)->setTime(20, 0);
                        if ($start->lt($current)) {
                            $start->addWeek();
                        }

                        if ($start->lte($end)) {
                            $endAt = $start->copy()->addHour();

                            if (!$this->isRoomTimeConflict($schedule->room_id, $start, $endAt)) {
                                $schedule->occurrences()->create([
                                    'recurrence_id' => $recurrence->id,
                                    'start_at'      => $start,
                                    'end_at'        => $endAt,
                                    'type'          => 'generated',
                                ]);
                            } else {
                                $skipped[] = $start->toDateTimeString();
                            }
                        }
                    }

                    $current->addWeeks($recurrence->interval);
                }
                break;

            // 毎月（第◯◯曜日）
            case 'monthly':
                while ($current->lte($end)) {
                    $date = $this->calculateDateFromRecurrence($recurrence, $current->year, $current->month);

                    if ($date) {
                        $start = $date->copy()->setTime(20, 0);
                        $endAt = $start->copy()->addHour();

                        if (!$this->isRoomTimeConflict($schedule->room_id, $start, $endAt)) {
                            $schedule->occurrences()->create([
                                'recurrence_id' => $recurrence->id,
                                'start_at'      => $start,
                                'end_at'        => $endAt,
                                'type'          => 'generated',
                            ]);
                        } else {
                            $skipped[] = $start->toDateTimeString();
                        }
                    }

                    $current->addMonths($recurrence->interval);
                }
                break;

            // 毎年（第◯◯曜日）
            case 'yearly':
                while ($current->lte($end)) {
                    $date = $this->calculateDateFromRecurrence($recurrence, $current->year, $current->month);

                    if ($date) {
                        $start = $date->copy()->setTime(20, 0);
                        $endAt = $start->copy()->addHour();

                        if (!$this->isRoomTimeConflict($schedule->room_id, $start, $endAt)) {
                            $schedule->occurrences()->create([
                                'recurrence_id' => $recurrence->id,
                                'start_at'      => $start,
                                'end_at'        => $endAt,
                                'type'          => 'generated',
                            ]);
                        } else {
                            $skipped[] = $start->toDateTimeString();
                        }
                    }

                    $current->addYears($recurrence->interval);
                }
                break;
        }

        return $skipped;
    }

    /**
     * monthly / yearly 用：byweekday / bysetpos から日付を計算
     */
    private function calculateDateFromRecurrence(ScheduleRecurrence $rec, int $year, int $month): ?Carbon
    {
        if (empty($rec->byweekday) || !isset($rec->byweekday[0])) {
            return null;
        }

        $weekday = $rec->byweekday[0];
        $weekpos = $rec->bysetpos;

        $carbonWeekday = $this->weekdayToCarbon($weekday);

        // 「その月の第 n 曜日」を求める
        $date = Carbon::create($year, $month, 1)->nthOfMonth($weekpos, $carbonWeekday)->setTime(20, 0);

        // start_after / until の範囲チェック
        if ($rec->start_after && $date->lt(Carbon::parse($rec->start_after))) {
            return null;
        }
        if ($rec->until && $date->gt(Carbon::parse($rec->until))) {
            return null;
        }

        return $date;
    }

    private function weekdayToCarbon(string $weekday)
    {
        return match ($weekday) {
            'MO' => Carbon::MONDAY,
            'TU' => Carbon::TUESDAY,
            'WE' => Carbon::WEDNESDAY,
            'TH' => Carbon::THURSDAY,
            'FR' => Carbon::FRIDAY,
            'SA' => Carbon::SATURDAY,
            'SU' => Carbon::SUNDAY,
        };
    }

    private function isRoomTimeConflict($roomId, Carbon $start, Carbon $end): bool
    {
        return ScheduleOccurrence::whereHas('schedule', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            })
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_at', '<=', $start)
                            ->where('end_at', '>=', $end);
                    });
            })
            ->exists();
    }
}
