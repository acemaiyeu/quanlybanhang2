<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateVariantValidator extends FormRequest
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
                    $fail('ID sản phẩm không tồn tại');
                }
            }],
            'variants_info' => 'required|array',
            'thumbnail' => 'required',
            'images' => 'required|array',
            'price' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'ID sản phẩm không được để trống',
            'variants_info.required' => 'Thông tin biến thể sản phẩm không được để trống',
            'variants_info.array' => 'Thông tin biến thể sản phẩm phải là mảng',
            'thumbnail.required' => 'Thumbnail biến thể sản phẩm không được để trống',
            'images.required' => 'Danh sách ảnh biến thể sản phẩm không được để trống',
            'images.array' => 'Danh sách ảnh biến thể sản phẩm phải là mảng',
            'price.required' => 'Giá biến thể sản phẩm không được để trống',
            'price.integer' => 'Giá biến thể sản phẩm phải là số',
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
