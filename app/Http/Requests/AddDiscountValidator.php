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

class AddDiscountValidator extends FormRequest
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
            'discount_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists =
                        Discount::whereNull('deleted_at')
                            ->where('id', $value)
                            ->where('start_date', '<=', Carbon::now('Asia/Ho_Chi_Minh'))
                            ->where('end_date', '>=', Carbon::now('Asia/Ho_Chi_Minh'))
                            ->where('active', 1)
                            ->exists();

                    if (!$exists) {
                        $fail('Mã giảm giá không tồn tại hoặc đã hết hạn');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID không được để trống',
            'discount_id.required' => 'ID Mã giảm giá không được để trống',
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
