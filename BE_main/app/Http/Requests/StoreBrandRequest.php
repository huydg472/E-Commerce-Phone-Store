<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreBrandRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:150', 'unique:brands,name'],
            'slug' => ['required', 'string', 'max:180', 'unique:brands,slug'],
            'type' => ['required', 'string', 'in:phone,accessory'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
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
        ];
    }
}
