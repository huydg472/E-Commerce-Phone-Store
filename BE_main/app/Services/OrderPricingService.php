<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class OrderPricingService
{
    public function generateOrderCode(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = 'ORD-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

            if (!Order::query()->where('order_code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'ORD-' . now()->format('YmdHis') . '-' . random_int(10000, 99999);
    }

    public function resolveShippingFee(string $shippingMethod): float
    {
        return match (strtolower(trim($shippingMethod))) {
            'standard' => 0,
            'express' => 40000,
            default => throw ValidationException::withMessages([
                'shipping_method' => ['Phương thức giao hàng không hợp lệ.'],
            ]),
        };
    }

    public function resolveCouponDiscount(string $couponCode, float $subtotal): array
    {
        if ($couponCode === '') {
            return [
                'coupon' => null,
                'discount_amount' => 0,
            ];
        }

        $coupon = Coupon::query()
            ->where('code', $couponCode)
            ->lockForUpdate()
            ->first();

        if (!$coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Mã giảm giá không tồn tại.'],
            ]);
        }

        if (!$coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Mã giảm giá hiện không khả dụng.'],
            ]);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Mã giảm giá chưa đến thời gian áp dụng.'],
            ]);
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Mã giảm giá đã hết hạn.'],
            ]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Mã giảm giá đã hết lượt sử dụng.'],
            ]);
        }

        if ($subtotal < (float)$coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã.'],
            ]);
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => $this->calculateCouponDiscount($coupon, $subtotal),
        ];
    }

    public function calculateCouponDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float)$coupon->value / 100)
            : (float)$coupon->value;

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float)$coupon->max_discount);
        }

        return max(min($discount, $subtotal), 0);
    }
}
