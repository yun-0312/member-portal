<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleUpdateRequest extends FormRequest
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
        return [
            'title' => ['required', 'string'],
            'schedule_category_id' => ['required', 'exists:schedule_categories,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'location' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
        ];
    }
}
