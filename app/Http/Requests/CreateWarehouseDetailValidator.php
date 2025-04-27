<?php

namespace App\Http\Requests;

use App\Models\Variant;
use App\Models\Warehouse;
use App\Models\WarehouseDetail;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class CreateWarehouseDetailValidator extends FormRequest
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
            'variant_id' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $warehouseId = $request->input('warehouse_id');
                    if (!$warehouseId) {
                        $fail('ID Kho hàng không được để trống');
                        return;
                    }

                    $variant = Variant::whereNull('deleted_at')->where('id', $value)->first();
                    if (!$variant) {
                        $fail('Biến thể sản phẩm không tồn tại');
                    }

                    $exists = WarehouseDetail::whereNull('deleted_at')
                        ->where('variant_id', $value)
                        ->where('warehouse_id', $warehouseId)
                        ->exists();

                    if ($exists) {
                        $fail('Dữ liệu này đã tồn tại trong Kho hàng.');
                    }
                },
            ],
            'warehouse_id' => ['required', function ($attribute, $value, $fail) use ($request) {
                $warehouse = Warehouse::whereNull('deleted_at')->where('id', $value)->first();
                if (!$warehouse) {
                    $fail('Kho hàng không tồn tại');
                }
            }],
            'quantity' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'variant_id.required' => 'ID Biến thể sản phẩm không được để trống',
            'warehouse_id.required' => 'ID Kho hàng không được để trống',
            'quantity.required' => 'Số lượng sản phẩm trong Chi tiết Kho hàng không được để trống',
            'quantity.numeric' => 'Số lượng sản phẩm phải là dạng số',
            'quantity.min' => 'Số lượng sản phẩm phải lớn hơn hoặc bằng 0',
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
