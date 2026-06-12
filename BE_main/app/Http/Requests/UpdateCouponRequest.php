<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id ?? $this->route('coupon');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Mã coupon',
            'name' => 'Tên coupon',
            'description' => 'Mô tả',
            'type' => 'Kiểu giảm giá',
            'value' => 'Giá trị giảm',
            'max_discount' => 'Giảm tối đa',
            'min_order_amount' => 'Đơn tối thiểu',
            'usage_limit' => 'Giới hạn lượt dùng',
            'starts_at' => 'Ngày bắt đầu',
            'ends_at' => 'Ngày kết thúc',
            'is_active' => 'Trạng thái',
        ];
    }
}

