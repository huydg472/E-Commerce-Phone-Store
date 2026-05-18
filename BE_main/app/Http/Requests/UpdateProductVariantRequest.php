<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variant = $this->route('product_variant') ?? $this->route('productVariant');
        $variantId = is_object($variant) ? $variant->id : $variant;

        $productId = $this->input('product_id', is_object($variant) ? $variant->product_id : null);
        $color = $this->input('color', is_object($variant) ? $variant->color : null);
        $storage = $this->input('storage', is_object($variant) ? $variant->storage : null);

        return [
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'color' => ['sometimes', 'required', 'string', 'max:100'],
            'storage' => ['sometimes', 'required', 'string', 'max:50'],
            'ram' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('product_variants')
                    ->where('product_id', $productId)
                    ->where('color', $color)
                    ->where('storage', $storage)
                    ->ignore($variantId),
            ],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('product_variants', 'sku')->ignore($variantId),
            ],
            'import_price' => ['nullable', 'numeric', 'min:0'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
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
            'status' => 'Trạng thái',
            'description' => 'Mô tả',
        ];
    }

}
