<?php

namespace App\Http\Requests;

use App\Models\Permission;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DetailPermissionValidator extends FormRequest
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
            'code' => [function ($attribute, $value, $fail) {
                $exists = Permission::whereNull('deleted_at')->where('code', $value)->exists();
                if (!$exists) {
                    $fail('Mã quyền không tồn tại');
                }
                if (auth()->user()->role->code != 'SUPER_ADMIN') {
                    $fail('Chỉ có Quản trị viên tối cao mới có thể xem thông tin quyền');
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
        return [];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'message' => 'Validation Failed'
        ], 422));
    }
}
