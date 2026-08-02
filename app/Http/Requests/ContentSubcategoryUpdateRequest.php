<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentSubcategoryUpdateRequest extends FormRequest
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
            'category_id' => ['sometimes', 'exists:content_categories,id'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer'],
            'display_type' => ['sometimes', 'string', Rule::in(['list', 'year_archive', 'children'])],
            'parent_id' => ['nullable', 'exists:content_subcategories,id'],
        ];
    }
}
