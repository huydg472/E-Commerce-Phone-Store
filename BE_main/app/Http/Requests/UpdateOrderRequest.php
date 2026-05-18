<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_status' => [
                'required',
                'string',
                'in:pending,confirmed,processing,shipping,completed,cancelled',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute không được để trống.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'in' => ':attribute không hợp lệ.',
        ];
    }

    public function attributes(): array
    {
        return [
            'order_status' => 'Trạng thái đơn hàng',
            'note' => 'Ghi chú',
        ];
    }
}
