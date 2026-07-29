<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
            $occurrence->show_url = "/admin/schedule-occurrences/{$occurrence->id}";
            return $occurrence;
        });

        return [
            'calendar' => $this->buildMonthlyCalendar($occurrences, $month),
            'month_links' => $this->buildMonthLinks($month),
            'year_links' => $this->buildYearLinks(substr($month, 0, 4)),
            'store_url' => "/admin/schedules",
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

        $scheduleArray['update_url'] = "/admin/schedules/{$schedule->id}";
        $scheduleArray['destroy_url'] = "/admin/schedules/{$schedule->id}";
        $scheduleArray['occurrences'] = $schedule->occurrences->map(function ($occurrence) {
            return [
                'id' => $occurrence->id,
                'start_at' => $occurrence->start_at,
                'end_at' => $occurrence->end_at,
                'type' => $occurrence->type,
                'update_url' => "/admin/schedule-occurrences/{$occurrence->id}",
                'destroy_url' => "/admin/schedule-occurrences/{$occurrence->id}",
            ];
        });

        return response()->json([
            'schedule' => $scheduleArray,
        ]);
    }

public function store(ScheduleStoreRequest $request)
    {
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated) {
                
                // 1. スケジュールの親レコード作成
                $schedule = Schedule::create([
                    'room_id'              => $validated['room_id'] ?? null,
                    'title'                => $validated['title'],
                    'schedule_category_id' => $validated['schedule_category_id'],
                    'location'             => $validated['location'] ?? null,
                    'url'                  => $validated['url'] ?? null,
                    'created_by'           => $request->user()->id,
                ]);

                $skipped = [];

                // 2. recurrence ありの場合（繰り返し予定）
                if (!empty($validated['recurrence'])) {
                    $recReq = $validated['recurrence'];

                    $recurrence = $schedule->recurrences()->create([
                        'frequency'   => $recReq['frequency'],
                        'byweekday'   => $recReq['byweekday'] ?? null,
                        'bysetpos'    => $recReq['bysetpos'] ?? null,
                        'interval'    => $recReq['interval'] ?? 1,
                        'until'       => $recReq['until'] ?? null,
                        'start_after' => $recReq['dtstart'] ?? null,
                    ]);

                    // Occurrence を生成
                    $skipped = $this->generateOccurrencesFromRecurrence(
                        $schedule, 
                        $recurrence, 
                        $recReq['start_time'], 
                        $recReq['end_time'], 
                        $schedule->id
                    );

                    // ★ 3. Occurrence が 1つも生成されなかった場合のバリデーションチェック
                    if ($schedule->occurrences()->count() === 0) {
                        // トランザクション内で例外を投げて DB 登録をロールバックさせる
                        throw new \Exception(
                            empty($skipped)
                                ? '指定された期間内に該当する予定（曜日・週位置）が存在しません。'
                                : '該当する予定がすべて会議室の重複のため登録できませんでした。',
                            422
                        );
                    }

                } else {
                    // 単発 occurrence 生成
                    $start = Carbon::parse($validated['start_at']);
                    $end   = Carbon::parse($validated['end_at']);

                    if ($this->isRoomTimeConflict($schedule->room_id, $start, $end)) {
                        throw new \Exception('この時間帯は既に予約されています', 422);
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
            });
        } catch (\Exception $e) {
            // エラーが発生した場合は 422 で返却（FormRequestと同じ形式でエラーレスポンスを返す）
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => [
                    'recurrence.until' => [$e->getMessage()], // Vue側の until フィールド下に赤字表示させる場合
                ]
            ], $e->getCode() === 422 ? 422 : 500);
        }
    }

    public function updateSchedule(ScheduleUpdateRequest $request, Schedule $schedule) {
        $schedule->update($request->validated());

        return response()->json([
            'message' => 'スケジュールを更新しました',
            'schedule' => $schedule->fresh(),
        ]);
    }

    public function showOccurrence(ScheduleOccurrence $occurrence) {
        $occurrence->load([
            'schedule.room',
            'schedule.category',
            'schedule.recurrences',
            'schedule.occurrences' => function ($query) {
                $query->orderBy('start_at', 'asc');
            },
        ]);

        $schedule = $occurrence->schedule;

        return response()->json([
            'occurrence' => [
                'id'          => $occurrence->id,
                'start_at'    => $occurrence->start_at,
                'end_at'      => $occurrence->end_at,
                'type'        => $occurrence->type,
                'update_url'  => "/admin/schedule-occurrences/{$occurrence->id}",
                'destroy_url' => "/admin/schedule-occurrences/{$occurrence->id}",
            ],
            'schedule' => [
                'id'                   => $schedule->id,
                'room_id'              => $schedule->room_id,
                'title'                => $schedule->title,
                'schedule_category_id' => $schedule->schedule_category_id,
                'location'             => $schedule->location,
                'url'                  => $schedule->url,
                'created_by'           => $schedule->created_by,
                'created_at'           => $schedule->created_at,
                'updated_at'           => $schedule->updated_at,
                'room'                 => $schedule->room,
                'category'             => $schedule->category,
                'recurrences'          => $schedule->recurrences,
                'show_url'             => "/admin/schedule-occurrences/{$schedule->id}",
                'update_url'           => "/admin/schedule-occurrences/{$schedule->id}",
                'destroy_url'          => "/admin/schedule-occurrences/{$schedule->id}",
                'occurrences'          => $schedule->occurrences->map(function ($item) {
                    return [
                        'id'          => $item->id,
                        'start_at'    => $item->start_at,
                        'end_at'      => $item->end_at,
                        'type'        => $item->type,
                        'update_url'  => "/admin/schedule-occurrences/{$item->id}/edit",
                        'destroy_url' => "/admin/schedule-occurrences/{$item->id}/delete",
                    ];
                }),
            ],
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
        $start = Carbon::parse($validated['start_at']);
        $end   = Carbon::parse($validated['end_at']);

        if ($this->isRoomTimeConflict($occurrence->schedule->room_id, $start, $end, $occurrence->id)) {
            return response()->json([
                'message' => 'この時間帯は既に予約されています',
            ], 422);
        }

        $occurrence->update([
            'recurrence_id' => null,
            'start_at'      => $start,
            'end_at'        => $end,
            'type'          => 'exception',
        ]);

        return response()->json([
            'message'    => 'この予定を更新しました',
            'occurrence' => $occurrence->load(['schedule', 'recurrence']),
        ]);
    }

    // これ以降の予定
    private function updateFuture(ScheduleOccurrence $occurrence, ScheduleRecurrence $recurrence, array $validated) {
        $recReq = $validated['recurrence'];
        $date   = Carbon::parse($recReq['dtstart'] ?? $occurrence->start_at);

        $recurrence->update([
            'until' => $date->copy()->subDay(),
        ]);

        $newRecurrence = $recurrence->schedule->recurrences()->create([
            'frequency'   => $recReq['frequency'],
            'byweekday'   => $recReq['byweekday'] ?? null,
            'bysetpos'    => $recReq['bysetpos'] ?? null,
            'interval'    => $recReq['interval'] ?? 1,
            'start_after' => $date,
            'until'       => $recReq['until'] ?? null,
        ]);

        ScheduleOccurrence::where('recurrence_id', $recurrence->id)
            ->where('start_at', '>=', $date)
            ->delete();

        $skipped = $this->generateOccurrencesFromRecurrence(
            $recurrence->schedule, 
            $newRecurrence, 
            $recReq['start_time'], 
            $recReq['end_time'], 
            $recurrence->schedule_id
        );

        return response()->json([
            'message' => empty($skipped) ? 'この予定以降を更新しました' : '一部の予定は重複のため更新されませんでした',
            'skipped' => $skipped,
        ]);
    }

    // すべて変更
    private function updateAll(
        Schedule $schedule,
        ScheduleRecurrence $recurrence,
        array $validated
    ) {
        $recReq = $validated['recurrence'];

        $recurrence->update([
            'frequency'   => $recReq['frequency'],
            'byweekday'   => $recReq['byweekday'] ?? null,
            'bysetpos'    => $recReq['bysetpos'] ?? null,
            'interval'    => $recReq['interval'] ?? 1,
            'until'       => $recReq['until'] ?? null,
            'start_after' => $recReq['dtstart'] ?? null,
        ]);

        $schedule->occurrences()->delete();

        $skipped = $this->generateOccurrencesFromRecurrence(
            $schedule, 
            $recurrence, 
            $recReq['start_time'], 
            $recReq['end_time'], 
            $schedule->id
        );

        return response()->json([
            'message' => empty($skipped) ? '全ての予定を更新しました' : '一部の予定は重複のため更新されませんでした',
            'skipped' => $skipped,
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        if ($schedule->recurrences()->exists()) {
            return response()->json([
                'message' => 'このスケジュールは繰り返し予定のため削除できません。',
            ], 422);
        }

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

        if (($mode === 'future' || $mode === 'all') && !$recurrence) {
            return response()->json([
                'message' => 'この予定は繰り返し予定ではありません',
            ], 422);
        }

        switch ($mode) {
            case 'single':
                $occurrence->delete();
                return response()->json([
                    'message' => 'この予定を削除しました'
                ]);

            case 'future':
                $date = Carbon::parse($occurrence->start_at);

                $recurrence->update([
                    'until' => $date->copy()->subDay(),
                ]);

                ScheduleOccurrence::where('recurrence_id', $recurrence->id)
                    ->where('start_at', '>=', $date)
                    ->delete();

                return response()->json([
                    'message' => 'この予定以降を削除しました'
                ]);

            case 'all':
                $recurrence->delete();
                $schedule->occurrences()->delete();
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
     * recurrence から occurrence を生成（リクエストから受け取った時刻を使用）
     */
    private function generateOccurrencesFromRecurrence(
        Schedule $schedule,
        ScheduleRecurrence $recurrence,
        string $startTime,
        string $endTime,
        $ignoreScheduleId = null
    ): array {
        $current = $recurrence->start_after
            ? Carbon::parse($recurrence->start_after)->startOfDay()
            : Carbon::now()->startOfMonth();

        $end = $recurrence->until
            ? Carbon::parse($recurrence->until)->endOfDay()
            : Carbon::now()->addYear()->endOfMonth();

        [$startHour, $startMin] = explode(':', $startTime);
        [$endHour, $endMin]     = explode(':', $endTime);

        $occurrencesToCreate = [];

        switch ($recurrence->frequency) {
            case 'daily':
                while ($current->lte($end)) {
                    $start = $current->copy()->setTime((int)$startHour, (int)$startMin);
                    $endAt = $current->copy()->setTime((int)$endHour, (int)$endMin);
                    $occurrencesToCreate[] = ['start' => $start, 'end' => $endAt];
                    $current->addDays($recurrence->interval);
                }
                break;

            case 'weekly':
                $weekdays = $recurrence->byweekday ?? [];
                $targetCarbonDays = array_map(fn($w) => $this->weekdayToCarbon($w), $weekdays);

                while ($current->lte($end)) {
                    if (in_array($current->dayOfWeek, $targetCarbonDays)) {
                        $start = $current->copy()->setTime((int)$startHour, (int)$startMin);
                        $endAt = $current->copy()->setTime((int)$endHour, (int)$endMin);
                        $occurrencesToCreate[] = ['start' => $start, 'end' => $endAt];
                    }

                    if ($current->dayOfWeek === Carbon::SUNDAY && $recurrence->interval > 1) {
                        $current->addWeeks($recurrence->interval - 1)->addDay();
                    } else {
                        $current->addDay();
                    }
                }
                break;

            case 'monthly':
            case 'yearly':
                $isYearly = $recurrence->frequency === 'yearly';
                while ($current->lte($end)) {
                    $dates = $this->calculateDatesFromRecurrence($recurrence, $current->year, $current->month);
                    foreach ($dates as $date) {
                        $start = $date->copy()->setTime((int)$startHour, (int)$startMin);
                        $endAt = $date->copy()->setTime((int)$endHour, (int)$endMin);
                        $occurrencesToCreate[] = ['start' => $start, 'end' => $endAt];
                    }
                    if ($isYearly) {
                        $current->addYears($recurrence->interval);
                    } else {
                        $current->addMonths($recurrence->interval);
                    }
                }
                break;
        }

        $skipped = [];

        // 重複チェックをしながら個別登録
        foreach ($occurrencesToCreate as $slot) {
            if ($this->isRoomTimeConflict($schedule->room_id, $slot['start'], $slot['end'], null, $ignoreScheduleId)) {
                $skipped[] = $slot['start']->toDateTimeString();
                continue;
            }

            $schedule->occurrences()->create([
                'recurrence_id' => $recurrence->id,
                'start_at'      => $slot['start'],
                'end_at'        => $slot['end'],
                'type'          => 'generated',
            ]);
        }

        return $skipped;
    }

    /**
     * monthly / yearly 用：byweekday / bysetpos から日付を計算
     */
    private function calculateDatesFromRecurrence(ScheduleRecurrence $rec, int $year, int $month): array {
        if (empty($rec->byweekday)) {
            return [];
        }

        $dates = [];
        $weekpos = $rec->bysetpos;

        foreach ($rec->byweekday as $weekday) {
            $carbonWeekday = $this->weekdayToCarbon($weekday);

            if ($weekpos) {
                $date = Carbon::create($year, $month, 1)->nthOfMonth($weekpos, $carbonWeekday);
            } else {
                $date = Carbon::create($year, $month, 1)->nthOfMonth(1, $carbonWeekday);
            }

            if ($rec->start_after && $date->lt(Carbon::parse($rec->start_after)->startOfDay())) {
                continue;
            }
            if ($rec->until && $date->gt(Carbon::parse($rec->until)->endOfDay())) {
                continue;
            }

            $dates[] = $date;
        }

        return $dates;
    }

    private function weekdayToCarbon(string $weekday) {
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

    private function isRoomTimeConflict($roomId, Carbon $start, Carbon $end, $ignoreOccurrenceId = null, $ignoreScheduleId = null): bool {
        if (!$roomId) {
            return false;
        }

        return ScheduleOccurrence::whereHas('schedule', function ($q) use ($roomId, $ignoreScheduleId) {
                $q->where('room_id', $roomId);
                if ($ignoreScheduleId) {
                    $q->where('id', '!=', $ignoreScheduleId);
                }
            })
            ->when($ignoreOccurrenceId, function ($q) use ($ignoreOccurrenceId) {
                $q->where('id', '!=', $ignoreOccurrenceId);
            })
            ->where(function ($query) use ($start, $end) {
                $query->where('start_at', '<', $end)
                    ->where('end_at', '>', $start);
            })
            ->exists();
    }
}