<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateDiscountValidator extends FormRequest
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
                $exists = Category::whereNull('deleted_at')->where('code', $value)->exists();
                if ($exists) {
                    $fail('Mã Khuyến mãi đã tồn tại');
                }
            }],
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'active' => 'required|in:0,1',
            'condition_apply' => 'required|in:ALL,SOME',
            'apply_for' => 'required|in:cart,product,shipping',
            'data' => 'required',
            'conditions' => ['required', function ($attribute, $value, $fail) {
                $conditions = $value;
                if (!is_array($conditions)) {
                    $fail('Điều kiện áp dụng Khuyến mãi phải là dạng mảng');
                } else {
                    foreach ($conditions as $condition) {
                        if (empty($condition['condition_apply'])) {
                            $fail('Điều kiện áp dụng Khuyến mãi không được để trống');
                        } else {
                            if ($condition['condition_apply'] != 'cart' && $condition['condition_apply'] != 'product') {
                                $fail('Điều kiện áp dụng Khuyến mãi phải là cart hoặc product');
                            }
                        }
                        if (empty($condition['condition_apply'])) {
                            $fail('Dữ liệu Điều kiện áp dụng Khuyến mãi không được để trống');
                        } else {
                            if (!is_array($condition['condition_data'])) {
                                $fail('Dữ liệu Điều kiện áp dụng Khuyến mãi phải là dạng mảng');
                            }
                        }
                    }
                }
            }]
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã Khuyến mãi không được để trống',
            'name.required' => 'Tên Khuyến mãi không được để trống',
            'start_date.required' => 'Thời gian bắt đầu Khuyến mãi không được để trống',
            'start_date.date' => 'Thời gian bắt đầu Khuyến mãi phải là dạng ngày',
            'end_date.required' => 'Thời gian kết Khuyến mãi không được để trống',
            'end_date.date' => 'Thời gian kết thúc Khuyến mãi phải là dạng ngày',
            'active.required' => 'Trạng thái Khuyến mãi không được để trống',
            'active.in' => 'Trạng thái Khuyến mãi phải là 0 (không hoạt động) hoặc 1 (đang hoạt động)',
            'condition_apply.required' => 'Số lượng điều kiện áp dụng Khuyến mãi không được để trống',
            'condition_apply.in' => 'Số lượng điều kiện áp dụng Khuyến mãi phải là ALL (Tất cả) hoặc SOME (Có ít nhất một)',
            'apply_for.required' => 'Loại mã khuyến mãi không được để trống',
            'apply_for.in' => 'Loại mã khuyến mãi phải là cart (Giảm giá Giỏ hàng), product (Giảm giá Sản phẩm) hoặc shipping (Giảm giá vận chuyển)',
            'data.required' => 'Dữ liệu Giảm giá Khuyến mãi không được để trống',
            'data.array' => 'Dữ liệu Giảm giá Khuyến mãi phải là dạng mảng',
            'conditions.required' => 'Điều kiện áp dụng Khuyến mãi không được để trống',
            'conditions.array' => 'Điều kiện áp dụng Khuyến mãi phải là dạng mảng',
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
