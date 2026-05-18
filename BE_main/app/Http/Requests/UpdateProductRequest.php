<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && ! $this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'brand_id' => ['sometimes', 'required', 'integer', 'exists:brands,id'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'thumbnail_url' => ['nullable', 'url', 'max:500'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive,draft'],
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
            'brand_id' => 'Thương hiệu',
            'category_id' => 'Danh mục',
            'name' => 'Tên sản phẩm',
            'slug' => 'Slug sản phẩm',
            'thumbnail_url' => 'Ảnh đại diện sản phẩm',
            'short_description' => 'Mô tả ngắn',
            'description' => 'Mô tả',
            'is_featured' => 'Sản phẩm nổi bật',
            'status' => 'Trạng thái',
        ];
    }

}
