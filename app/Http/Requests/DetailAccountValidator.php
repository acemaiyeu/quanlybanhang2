<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DetailAccountValidator extends FormRequest
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
            'id' => [function ($attribute, $value, $fail) {
                $user = User::whereNull('deleted_at')->where('id', $value)->with('role')->select('id', 'role_id')->first();
                if (!$user) {
                    $fail('Tài khoản không tồn tại');
                    return;
                }
                if (empty($user->role)) {
                    $fail('Tài khoản khóa');
                } else {
                    if ($user->role->code != 'GUEST' && auth()->user()->role->code != 'SUPER_ADMIN') {
                        $fail('Tài khoản của bạn không có quyền xem tài khoản này');
                    }
                }
            }]
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
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
