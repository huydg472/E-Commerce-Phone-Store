<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['sometimes', 'required', 'integer', 'exists:product_variants,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'type' => ['sometimes', 'required', 'string', 'in:import,sale,cancel_order,return,adjustment'],
            'quantity_before' => ['sometimes', 'required', 'integer', 'min:0'],
            'quantity_change' => ['sometimes', 'required', 'integer', 'not_in:0'],
            'quantity_after' => ['sometimes', 'required', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
        ];
    }
}
