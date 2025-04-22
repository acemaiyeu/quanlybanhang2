<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordValidator
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
            'email' => 'required|email|exists:users',
            'password' => 'required',
            'new_password' => 'required',
            'confirm_new_password' => 'required|same:new_password',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email không được để trống',
            'email.email' => 'Sai định dạng email',
            'email.exists' => 'Email không tồn tại trong hệ thống',
            'password.required' => 'Mật khẩu không được để trống',
            'new_password.required' => 'Mật khẩu mới không được để trống',
            'confirm_new_password.required' => 'Xác nhận mật khẩu mới không được để trống',
            'confirm_new_password.same' => 'Mật khẩu xác nhận không khớp với mật khẩu mới.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'message' => 'Validation Failed'
        ], 422));
    }
}
