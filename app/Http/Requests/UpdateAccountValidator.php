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
use Illuminate\Http\Request;

class UpdateAccountValidator extends FormRequest
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
    public function rules(Request $request): array
    {
        return [
            'id' => ['required', function ($attribute, $value, $fail) {
                $exists = User::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('Tài khoản không tồn tại');
                }
            }],
            'email' => ['email', function ($attribute, $value, $fail) use ($request) {
                if (!empty($request->input('id'))) {
                    $user = User::whereNull('deleted_at')->where('email', $value)->where('id', '!=', $request->input('id'))->first();
                    if ($user) {
                        $fail('Email đã tồn tại');
                    }
                }
            }],
            'role_id' => [function ($attribute, $value, $fail) {
                $role = Role::whereNull('deleted_at')->where('id', $value)->select('id', 'code', 'name')->first();
                if (!$role) {
                    $fail('Quyền tài khoản không tồn tại');
                    return;
                }
                if ($role->code != 'GUEST' && auth()->user()->role->code != 'SUPER_ADMIN') {
                    $fail('Tài khoản của bạn không có quyền tạo tài khoản có quyền cao hơn Khách hàng');
                    return;
                }
            }],
            'fullname' => 'min:15',
            'phone' => ['min:10', 'max:10', function ($attribute, $value, $fail) use ($request) {
                if (!empty($request->input('id'))) {
                    $exists = User::whereNull('deleted_at')->where('phone', $value)->where('id', '!=', $request->input('id'))->exists();
                    if ($exists) {
                        $fail('Số Điện thoại đã tồn tại');
                    }
                }
            }],
            'city_id' => function ($attribute, $value, $fail) {
                $exists = City::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('Tinh/Thành phố không tồn tại');
                }
            },
            'district_id' => function ($attribute, $value, $fail) {
                $exists = District::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('Quận/Huyện không tồn tại');
                }
            },
            'ward_id' => function ($attribute, $value, $fail) {
                $exists = Ward::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('Phường/Xã không tồn tại');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'ID tài khoản không được để trống',
            'email.email' => 'Email bạn nhập vào phải đúng dạng email',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.string' => 'Mật khẩu phải là dạng chuỗi',
            'fullname.min' => 'Họ và tên phải có ít nhất 15 ký tự',
            'phone.min' => 'Số điện thoại phải có 10 ký tự',
            'phone.max' => 'Số điện thoại phải có 10 ký tự',
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
