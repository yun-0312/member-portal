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
            $rules['recurrence.frequency'] = ['required', 'in:daily,weekly,monthly,yearly'];
            $rules['recurrence.byweekday'] = ['required', 'array'];
            $rules['recurrence.byweekday.*'] = ['string'];
            $rules['recurrence.bysetpos'] = ['required', 'integer'];
            $rules['recurrence.interval'] = ['required', 'integer', 'min:1'];
            $rules['recurrence.until'] = ['nullable', 'date'];
        }

        return $rules;
    }
}
