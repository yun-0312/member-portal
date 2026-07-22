<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ContentCategory;

class ContentCategoryUpdateRequest extends FormRequest
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
        $category = $this->route('content_category') ?? $this->route('category') ?? $this->route('id');
        $categoryId = $category instanceof ContentCategory ? $category->id : $category;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('content_categories', 'slug')->ignore($categoryId),],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
