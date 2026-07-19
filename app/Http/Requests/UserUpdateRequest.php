<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            'password' => ['sometimes', 'string', 'min:8'],
            'role_id' => ['sometimes', 'exists:roles,id'],
            'status' => ['sometimes', 'integer', 'between:0,9'],
            'approved_at' => ['sometimes', 'date'],
            'approved_by' => ['sometimes', 'exists:users,id'],
            'medical_institution_id' => ['sometimes', 'exists:medical_institutions,id'],
        ];
    }
}
