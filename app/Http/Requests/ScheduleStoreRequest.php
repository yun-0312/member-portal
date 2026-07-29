<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hasRecurrence = $this->filled('recurrence');

        // recurrence がある場合（繰り返し予定）
        if ($hasRecurrence) {
            return [
                'room_id' => ['nullable', 'integer'],
                'title' => ['required', 'string', 'max:255'],
                'schedule_category_id' => ['required', 'integer'],
                'location' => ['nullable', 'string', 'max:255'],
                'url' => ['nullable', 'url', 'max:255'],

                'recurrence' => ['required', 'array'],

                // ★追加: 繰り返し開始日・時刻のバリデーション
                'recurrence.dtstart' => ['required', 'date'],
                'recurrence.start_time' => ['required', 'date_format:H:i'],
                'recurrence.end_time' => ['required', 'date_format:H:i', 'after:recurrence.start_time'],

                'recurrence.frequency' => ['required', 'in:daily,weekly,monthly,yearly'],

                // byweekday: daily 以外（weekly, monthly, yearly）の時は必須
                'recurrence.byweekday' => ['required_unless:recurrence.frequency,daily', 'nullable', 'array'],
                'recurrence.byweekday.*' => ['string', 'in:MO,TU,WE,TH,FR,SA,SU'],

                // bysetpos: monthly の時だけ必須（他は nullable）
                'recurrence.bysetpos' => ['required_if:recurrence.frequency,monthly', 'nullable', 'integer', 'in:1,2,3,4,-1'],

                'recurrence.interval' => ['required', 'integer', 'min:1'],
                'recurrence.until' => ['nullable', 'date', 'after_or_equal:recurrence.dtstart'],
            ];
        }

        // recurrence がない場合（単発予定）
        return [
            'room_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'schedule_category_id' => ['required', 'integer'],
            'location' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],

            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
        ];
    }
}