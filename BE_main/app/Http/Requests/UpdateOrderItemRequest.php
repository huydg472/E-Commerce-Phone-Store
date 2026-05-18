<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'product_name' => ['sometimes', 'required', 'string', 'max:200'],
            'variant_name' => ['sometimes', 'required', 'string', 'max:200'],
            'sku' => ['nullable', 'string', 'max:100'],
            'unit_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'total_price' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }


    public function messages(): array
    {
        return [
            'required' => ':attribute không được để trống.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'integer' => ':attribute phải là số nguyên.',
            'numeric' => ':attribute phải là số.',
            'boolean' => ':attribute phải là true hoặc false.',
            'array' => ':attribute phải là một danh sách.',
            'date' => ':attribute phải là ngày hợp lệ.',
            'url' => ':attribute phải là đường dẫn hợp lệ.',
            'email' => ':attribute phải là email hợp lệ.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'min' => ':attribute phải có giá trị tối thiểu là :min.',
            'unique' => ':attribute đã tồn tại.',
            'exists' => ':attribute không tồn tại trong hệ thống.',
            'in' => ':attribute không hợp lệ.',
            'confirmed' => ':attribute xác nhận không khớp.',
            'not_in' => ':attribute không được bằng :values.',
            'lte' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_variant_id' => 'Biến thể sản phẩm',
            'product_name' => 'Tên sản phẩm',
            'variant_name' => 'Tên biến thể',
            'sku' => 'SKU',
            'unit_price' => 'Đơn giá',
            'quantity' => 'Số lượng',
            'total_price' => 'Thành tiền',
        ];
    }

}
