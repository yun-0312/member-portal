<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoticeUpdateRequest extends FormRequest
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
            'category_id'   => ['sometimes', 'exists:notice_categories,id'],
            'title'         => ['sometimes', 'string', 'max:255'],
            'body'          => ['nullable', 'string'],
            'published_at'  => ['sometimes', 'date'],
            'committee_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'タイトル',
            'body' => '本文',
            'committee_name' => '委員会名',
            'category_id' => 'カテゴリ',
            'published_at' => '公開日',
        ];
    }
}
