<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Variant;
use App\Models\Warehouse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateInventoryValidator extends FormRequest
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
            'product_id' => ['required', function ($attribute, $value, $fail) {
                $exists = Product::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('ID  sản phẩm không tồn tại');
                }
            }],
            'variant_id' => ['required', function ($attribute, $value, $fail) {
                $exists = Variant::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('ID Biến thể sản phẩm không đã tồn tại');
                }
            }],
            'warehouse_id' => ['required', function ($attribute, $value, $fail) {
                $exists = Warehouse::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('Id Kho hàng không tồn tại');
                }
            }],
            'unit_id' => ['required', function ($attribute, $value, $fail) {
                $exists = Unit::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('Id Đơn vị sản phẩm không tồn tại');
                }
            }],
            'order_id' => ['required', function ($attribute, $value, $fail) {
                $exists = Order::whereNull('deleted_at')->where('id', $value)->exists();
                if (!$exists) {
                    $fail('Id Đơn vị sản phẩm không tồn tại');
                }
            }],
            'quantity' => 'required|integer:min:1',
            'location' => 'required',
            'status' => 'required|in:IMPORT,EXPORT',
            'batch_number' => 'required',
            'expiration_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'ID sản phẩm không được để trống',
            'variant_id.required' => 'ID Biến thể sản phẩm không được để trống',
            'warehouse_id.required' => 'Id Kho hàng không được để trống',
            'unit_id.required' => 'Id Đơn vị sản phẩm không được để trống',
            'quantity.required' => 'Số lượng không được để trống',
            'quantity.integer' => 'Số lượng phải là số nguyên',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
            'location.required' => 'Vị trí Nhập/Xuất kho không được để trống',
            'status.required' => 'Trạng thái Nhập/Xuất kho không được để trống',
            'status.in' => 'Trạng thái Nhập/Xuất kho phải là IMPORT (Nhập kho) hoặc EXPORT (Xuất kho)',
            'batch_number.required' => 'Mã số lô hàng không được để trống',
            'expiration_date.required' => 'Ngày hết hạn sản phẩm không được để trống',
            'expiration_date.date' => 'Ngày hết hạn sản phẩm phải là dạng ngày',
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
