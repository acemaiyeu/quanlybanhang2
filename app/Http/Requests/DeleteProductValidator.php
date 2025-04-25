<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteProductValidator extends FormRequest
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
                $exists = Product::whereNull('deleted_at')->where('code', $value)->exists();
                if (!$exists) {
                    $fail('Mã sản phẩm không tồn tại');
                }
            }]
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
            'code.required' => 'Mã sản phẩm không được để trống',
            'name.required' => 'Tên sản phẩm không được để trống',
            'category_id.required' => 'Id Loại sản phẩm không được để trống',
            'unit_id.required' => 'Id Đơn vị sản phẩm không được để trống',
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
