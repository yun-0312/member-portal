<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['nullable', 'integer', 'between:0,9'],
            'approved_at' => ['nullable', 'date'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'medical_institution_id' => ['nullable', 'exists:medical_institutions,id'],
        ];
    }

        public function messages()
    {
        return [
            'name.required' => '名前は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.unique' => 'このメールアドレスは既に使用されています',
            'password.required' => 'パスワードは必須です',
            'role_id.required' => 'ロールを選択してください',
        ];
    }
}
