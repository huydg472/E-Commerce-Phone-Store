<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $image = $this->route('product_variant_image') ?? $this->route('productVariantImage');
        $imageId = is_object($image) ? $image->id : $image;

        $variantId = $this->input(
            'product_variant_id',
            is_object($image) ? $image->product_variant_id : null
        );

        return [
            'product_variant_id' => ['sometimes', 'required', 'integer', 'exists:product_variants,id'],
            'image_url' => [
                'sometimes',
                'required',
                'url',
                'max:500',
                Rule::unique('product_variant_images')
                    ->where('product_variant_id', $variantId)
                    ->ignore($imageId),
            ],
            'alt_text' => ['nullable', 'string', 'max:225'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
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
            'image_url' => 'Đường dẫn ảnh',
            'alt_text' => 'Mô tả ảnh',
            'sort_order' => 'Thứ tự hiển thị',
        ];
    }

}
