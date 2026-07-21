<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoStoreRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'external_url' => ['required', 'url'],
            'published_at' => ['required', 'date'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:published_at'],

            'file' => ['nullable', 'array'],
            'file.*' => ['file', 'max:10240'],

            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }

    public function messages()
    {
        return [
            'type.required' => '動画の種類を選択してください。',
            'type.in' => '動画の種類が不正です。',
            'title.required' => 'タイトルは必須です。',
            'external_url.url' => 'URLの形式が不正です。',
            'published_at.required' => '公開開始日は必須です。',
            'expired_at.after_or_equal' => '公開終了日は公開開始日以降の日付を指定してください。',
        ];
    }
}
