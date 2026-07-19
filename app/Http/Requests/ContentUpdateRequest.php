<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentUpdateRequest extends FormRequest
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
            'body' => ['nullable', 'string'],
            'category_id' => ['sometimes', 'exists:content_categories,id'],
            'subcategory_id' => ['nullable', 'exists:content_subcategories,id'],
            'published_at' => ['sometimes', 'date'],
        ];
    }

    public function messages() {
        return [
            'title.required' => 'タイトルは必須です',
            'category_id.sometimes' => 'カテゴリを選択してください',
            'published_at.required' => '公開日を入力してください'
        ];
    }
}
