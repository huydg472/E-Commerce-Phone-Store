<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['nullable', 'integer', 'exists:shipping_addresses,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'receiver_name' => ['required', 'string', 'max:150'],
            'receiver_phone' => ['required', 'string', 'max:20'],
            'shipping_address_text' => ['required', 'string'],
            'shipping_fee' => ['sometimes', 'numeric', 'min:0'],
            'discount_amount' => ['sometimes', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'string', 'in:cod,bank_transfer,vnpay,momo'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.product_name' => ['required', 'string', 'max:200'],
            'items.*.variant_name' => ['required', 'string', 'max:200'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.total_price' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
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
            'shipping_address_id' => 'Địa chỉ giao hàng',
            'coupon_code' => 'Mã coupon',
            'receiver_name' => 'Tên người nhận',
            'receiver_phone' => 'Số điện thoại người nhận',
            'shipping_address_text' => 'Địa chỉ giao hàng',
            'shipping_fee' => 'Phí vận chuyển',
            'discount_amount' => 'Số tiền giảm giá',
            'payment_method' => 'Phương thức thanh toán',
            'note' => 'Ghi chú',
        ];
    }
}
