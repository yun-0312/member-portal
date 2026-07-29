<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OccurrenceUpdateRequest extends FormRequest
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
        $mode = $this->input('mode');

        $rules = [
            'mode' => ['required', 'in:single,future,all'],
        ];

        if ($mode === 'single') {
            // この予定だけ → start_at / end_at 必須
            $rules['start_at'] = ['required', 'date'];
            $rules['end_at'] = ['required', 'date', 'after_or_equal:start_at'];
        }

        if ($mode === 'future' || $mode === 'all') {
            // これ以降 / すべて → recurrence 必須
            $rules['recurrence'] = ['required', 'array'];

            // ★追加: 繰り返しの適用開始日・時刻のバリデーション
            $rules['recurrence.dtstart'] = ['required', 'date'];
            $rules['recurrence.start_time'] = ['required', 'date_format:H:i'];
            $rules['recurrence.end_time'] = ['required', 'date_format:H:i', 'after:recurrence.start_time'];

            $rules['recurrence.frequency'] = ['required', 'in:daily,weekly,monthly,yearly'];

            // byweekday: daily 以外（weekly, monthly, yearly）の時は必須
            $rules['recurrence.byweekday'] = ['required_unless:recurrence.frequency,daily', 'nullable', 'array'];
            $rules['recurrence.byweekday.*'] = ['string', 'in:MO,TU,WE,TH,FR,SA,SU'];

            // bysetpos: monthly の時だけ必須（他は nullable）
            $rules['recurrence.bysetpos'] = ['required_if:recurrence.frequency,monthly', 'nullable', 'integer', 'in:1,2,3,4,-1'];

            $rules['recurrence.interval'] = ['required', 'integer', 'min:1'];
            $rules['recurrence.until'] = ['nullable', 'date', 'after_or_equal:recurrence.dtstart'];
        }

        return $rules;
    }
}