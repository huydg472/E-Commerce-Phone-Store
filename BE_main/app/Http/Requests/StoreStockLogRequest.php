<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'type' => ['required', 'string', 'in:import,sale,cancel_order,return,adjustment'],
            'quantity_before' => ['required', 'integer', 'min:0'],
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'quantity_after' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_variant_id' => 'Biến thể sản phẩm',
            'user_id' => 'Người tạo',
            'order_id' => 'Đơn hàng',
            'type' => 'Loại thay đổi kho',
            'quantity_before' => 'Số lượng trước',
            'quantity_change' => 'Số lượng thay đổi',
            'quantity_after' => 'Số lượng sau',
            'note' => 'Ghi chú',
        ];
    }
}
