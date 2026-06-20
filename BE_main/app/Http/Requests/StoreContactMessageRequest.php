<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'subject' => ['required', Rule::in(['product_advice', 'order_support', 'warranty', 'feedback'])],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute khong duoc de trong.',
            'string' => ':attribute phai la chuoi ky tu.',
            'email' => ':attribute phai la email hop le.',
            'max' => ':attribute khong duoc vuot qua :max ky tu.',
            'in' => ':attribute khong hop le.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Ho va ten',
            'email' => 'Email',
            'phone' => 'So dien thoai',
            'subject' => 'Chu de',
            'message' => 'Noi dung',
        ];
    }
}
