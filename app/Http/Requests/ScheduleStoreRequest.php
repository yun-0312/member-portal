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

        // recurrence がある場合 → start_at / end_at は不要
        if ($hasRecurrence) {
            return [
                'room_id' => ['nullable', 'integer'],
                'title' => ['required', 'string', 'max:255'],
                'schedule_category_id' => ['required', 'integer'],
                'location' => ['nullable', 'string'],
                'url' => ['nullable', 'url'],

                'recurrence' => ['required', 'array'],
                'recurrence.frequency' => ['required', 'in:daily,weekly,monthly,yearly'],
                'recurrence.byweekday' => ['required', 'array'],
                'recurrence.byweekday.*' => ['string'],
                'recurrence.bysetpos' => ['required', 'integer'],
                'recurrence.interval' => ['required', 'integer', 'min:1'],
                'recurrence.until' => ['nullable', 'date'],
            ];
        }

        // recurrence がない場合 → start_at / end_at 必須（単発）
        return [
            'room_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'schedule_category_id' => ['required', 'integer'],
            'location' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],

            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
        ];
    }

}
