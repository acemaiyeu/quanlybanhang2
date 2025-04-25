<?php

namespace App\Http\Requests;

use App\Models\Promotion;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePromotionValidator extends FormRequest
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
                $exists = Promotion::whereNull('deleted_at')->where('code', $value)->exists();
                if (!$exists) {
                    $fail('Mã Chương trình Khuyến mãi không tồn tại');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã Chương trình Khuyến mãi không được để trống'
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
