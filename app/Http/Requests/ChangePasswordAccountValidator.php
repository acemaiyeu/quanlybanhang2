<?php

namespace App\Http\Requests;

use App\Models\City;
use App\Models\District;
use App\Models\Role;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordAccountValidator extends FormRequest
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
            'email' => ['required', 'email', function ($attribute, $value, $fail) {
                $exists = User::whereNull('deleted_at')->where('email', $value)->exists();
                if (!$exists) {
                    $fail('Email không tồn tại');
                }
            }],
            'new_password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email bạn nhập vào phải đúng dạng email',
            'new_password.required' => 'Mật khẩu không được để trống',
            'new_password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
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
