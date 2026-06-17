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
            'sku' => ['nullable', 'string', 'max:100', 'unique:product_variants,sku'],
            'import_price' => ['nullable', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute khÃ´ng Ä‘Æ°á»£c Ä‘á»ƒ trá»‘ng.',
            'string' => ':attribute pháº£i lÃ  chuá»—i kÃ½ tá»±.',
            'integer' => ':attribute pháº£i lÃ  sá»‘ nguyÃªn.',
            'numeric' => ':attribute pháº£i lÃ  sá»‘.',
            'boolean' => ':attribute pháº£i lÃ  true hoáº·c false.',
            'array' => ':attribute pháº£i lÃ  má»™t danh sÃ¡ch.',
            'date' => ':attribute pháº£i lÃ  ngÃ y há»£p lá»‡.',
            'url' => ':attribute pháº£i lÃ  Ä‘Æ°á»ng dáº«n há»£p lá»‡.',
            'email' => ':attribute pháº£i lÃ  email há»£p lá»‡.',
            'max' => ':attribute khÃ´ng Ä‘Æ°á»£c vÆ°á»£t quÃ¡ :max kÃ½ tá»±.',
            'min' => ':attribute pháº£i cÃ³ giÃ¡ trá»‹ tá»‘i thiá»ƒu lÃ  :min.',
            'unique' => ':attribute Ä‘Ã£ tá»“n táº¡i.',
            'exists' => ':attribute khÃ´ng tá»“n táº¡i trong há»‡ thá»‘ng.',
            'in' => ':attribute khÃ´ng há»£p lá»‡.',
            'confirmed' => ':attribute xÃ¡c nháº­n khÃ´ng khá»›p.',
            'not_in' => ':attribute khÃ´ng Ä‘Æ°á»£c báº±ng :values.',
            'lte' => ':attribute pháº£i nhá» hÆ¡n hoáº·c báº±ng :value.',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'Sáº£n pháº©m',
            'color' => 'MÃ u sáº¯c',
            'storage' => 'Bá»™ nhá»›',
            'ram' => 'RAM',
            'sku' => 'SKU',
            'import_price' => 'GiÃ¡ nháº­p',
            'price' => 'GiÃ¡ bÃ¡n',
            'sale_price' => 'GiÃ¡ khuyáº¿n mÃ£i',
            'quantity' => 'Sá»‘ lÆ°á»£ng',
            'is_featured' => 'Ná»•i báº­t',
            'description' => 'MÃ´ táº£',
        ];
    }
}
