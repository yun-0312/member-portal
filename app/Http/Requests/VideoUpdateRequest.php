<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoUpdateRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url'],
            'published_at' => ['sometimes', 'date'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:published_at'],

            'file' => ['nullable', 'array'],
            'file.*' => ['file', 'max:10240'],
            // 削除用
            'delete_file_ids' => ['nullable', 'array'],
            'delete_file_ids.*' => ['integer'],
            'delete_all_files' => ['boolean'],

            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
