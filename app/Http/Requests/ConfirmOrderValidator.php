<?php

namespace App\Http\Requests;

use App\Models\Cart;
use App\Models\Discount;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ConfirmOrderValidator extends FormRequest
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
            'session_id' => ['required', function ($attribute, $value, $fail) {
                $exists = Cart::whereNull('deleted_at')->where('session_id', $value)->exists();
                if (!$exists) {
                    $fail('Giỏ hàng không tồn tại');
                }
            }],
            'fullname' => ['required', 'string', 'max:255'],
            'user_phone' => ['required', 'string', 'max:255'],
            'user_address' => ['required', 'string', 'max:255'],
            'user_address' => ['required', 'string', 'max:255'],
            'method_payment' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID không được để trống',
            'fullname.required' => 'Họ tên không được để trống',
            'user_phone.required' => 'Sđt không được để trống',
            'user_address.required' => 'Địa chỉ nhận hàng không được để trống',
            'method_payment.required' => 'Phương thức thanh toán không được để trống',
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
