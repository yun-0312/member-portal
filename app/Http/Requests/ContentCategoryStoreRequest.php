<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentCategoryStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:content_categories,slug'],
            'section' => ['required', 'string', Rule::in(['download', 'main_menu', 'special'])],
            'sort_order' => ['nullable', 'integer'],
            'display_type' => ['nullable', 'string', Rule::in(['list', 'year_archive', 'subcategory'])],
        ];
    }
}
