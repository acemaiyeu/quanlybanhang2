<?php

namespace App\Http\Requests;

use App\Models\Discount;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateWarehouseValidator extends FormRequest
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
            'code' => ['required', function ($attribute, $value, $fail) {
                $exists = Warehouse::whereNull('deleted_at')->where('code', $value)->exists();
                if ($exists) {
                    $fail('Mã Kho hàng đã tồn tại');
                }
            }],
            'name' => 'required',
            'distributor_id' => ['required', function ($attribute, $value, $fail) {
                $exists = User::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('ID Nhà cung cấp không tồn tại');
                }
            }],
            'address' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã Kho hàng không được để trống',
            'name.required' => 'Tên Kho hàng không được để trống',
            'distributor_id.required' => 'ID Nhà cung cấp không được để trống',
            'address.required' => 'Địa chỉ Kho hàng không được để trống',
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
