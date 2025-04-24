<?php

namespace App\Http\Requests;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateOrderValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Request $request): bool
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
            'code' => ['required', function ($attribute, $value, $fail) {
                $exists = Order::whereNull('deleted_at')->where('code', $value)->exists();
                if (!$exists) {
                    $fail('Đơn hàng không tồn tại');
                }
            }],
        ];
    }

    public function validationData()
    {
        // Gộp dữ liệu từ URL (route param) vào để nó được validate
        return array_merge($this->all(), [
            'code' => $this->route('code'),
        ]);
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã đơn hàng không được để trống'
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
