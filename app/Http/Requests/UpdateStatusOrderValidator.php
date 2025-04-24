<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStatusOrderValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', function ($attribute, $value, $fail) {
                $exists = Order::whereNull('deleted_at')->where('code', $value)->exists();
                if (!$exists) {
                    $fail('Đơn hàng không tồn tại');
                }
            }],
            'order_status_id' => ['required', 'integer', 'exists:order_status,id'],
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), [
            'code' => $this->route('code'),
        ]);
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã đơn hàng không được để trống',
            'order_status_id.required' => 'Trạng thái đơn hàng không được để trống',
            'order_status_id.integer' => 'Trạng thái đơn hàng phải là số',
            'order_status_id.exists' => 'Trạng thái đơn hàng không tồn tại',
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
