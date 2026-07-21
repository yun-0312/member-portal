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
            'category_id' => ['sometimes', 'integer', 'exists:content_categories,id'],
            'group_id' => ['sometimes', 'integer', 'exists:groups,id'],
            'subcategory_id' => ['nullable', 'exists:content_subcategories,id'],
            'published_at' => ['sometimes', 'date'],

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

    public function messages() {
        return [
            'title.required' => 'タイトルは必須です',
            'category_id.sometimes' => 'カテゴリを選択してください',
            'published_at.required' => '公開日を入力してください'
        ];
    }
}
