<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute không được để trống.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'numeric' => ':attribute phải là số.',
            'min' => ':attribute phải có giá trị tối thiểu là :min.',
            'max' => ':attribute không được vượt quá :max ký tự.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Mã coupon',
            'subtotal' => 'Tạm tính',
        ];
    }
}

