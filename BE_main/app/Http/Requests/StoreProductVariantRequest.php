<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'color' => ['required', 'string', 'max:100'],
            'storage' => ['required', 'string', 'max:50'],
            'ram' => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_variants')
                    ->where('product_id', $this->product_id)
                    ->where('color', $this->color)
                    ->where('storage', $this->storage),
            ],
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'import_price' => ['nullable', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
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
            'product_id' => 'Sản phẩm',
            'color' => 'Màu sắc',
            'storage' => 'Bộ nhớ',
            'ram' => 'RAM',
            'sku' => 'SKU',
            'import_price' => 'Giá nhập',
            'price' => 'Giá bán',
            'sale_price' => 'Giá khuyến mãi',
            'quantity' => 'Số lượng',
            'description' => 'Mô tả',
        ];
    }

}
