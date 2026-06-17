<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && !$this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    public function rules(): array
    {
        $brand = $this->route('brand');
        $brandId = is_object($brand) ? $brand->id : $brand;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('brands', 'name')->ignore($brandId),
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                Rule::unique('brands', 'slug')->ignore($brandId),
            ],
            'type' => ['sometimes', 'required', 'string', 'in:phone,accessory'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute khong duoc de trong.',
            'string' => ':attribute phai la chuoi ky tu.',
            'url' => ':attribute phai la duong dan hop le.',
            'max' => ':attribute khong duoc vuot qua :max ky tu.',
            'unique' => ':attribute da ton tai.',
            'in' => ':attribute khong hop le.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Ten thuong hieu',
            'slug' => 'Slug thuong hieu',
            'type' => 'Loai thuong hieu',
            'logo_url' => 'Duong dan logo',
            'description' => 'Mo ta',
            'status' => 'Trang thai',
        ];
    }
}
