<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AddToCartValidator extends FormRequest
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
            'session_id' => 'required',
            'variant_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists =
                        Warehouse::whereNull('deleted_at')
                            ->whereHas('details', function ($query) use ($value) {
                                $query->where('variant_id', $value)->whereNull('deleted_at')->where('quantity', '>', 0);
                            })
                            ->exists();

                    if (!$exists) {
                        $fail('Biến thể sản phẩm không tồn tại trong kho hoặc đã hết hàng.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID không được để trống',
            'variant_id.required' => 'Variant ID không được để trống',
            'variant_id.exists' => 'Biến thể sản phẩm không tồn tại trong kho hoặc đã hết hàng.',
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
