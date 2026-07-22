<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\NoticeCategory;

class NoticeCategoryUpdateRequest extends FormRequest
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
        $parameter = collect($this->route()->parameters())->first();
        $categoryId = $parameter instanceof NoticeCategory ? $parameter->id : $parameter;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('notice_categories', 'slug')->ignore($categoryId)],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
