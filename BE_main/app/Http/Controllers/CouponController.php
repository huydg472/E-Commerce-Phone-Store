<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index()
    {
        $this->authorizeCouponAction(request(), 'coupons.view');

        $coupons = Coupon::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $this->authorizeCouponAction($request, 'coupons.create');

        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;
        $data['used_count'] = 0;

        $coupon = Coupon::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tạo coupon thành công.',
            'data' => $coupon,
        ], 201);
    }

    public function show(Coupon $coupon): JsonResponse
    {
        $this->authorizeCouponAction(request(), 'coupons.view');

        return response()->json([
            'success' => true,
            'data' => $coupon,
        ]);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse
    {
        $this->authorizeCouponAction($request, 'coupons.update');

        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', $coupon->is_active);
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;

        $coupon->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật coupon thành công.',
            'data' => $coupon->fresh(),
        ]);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $this->authorizeCouponAction(request(), 'coupons.delete');

        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa coupon thành công.',
        ]);
    }

    public function toggleStatus(Coupon $coupon): JsonResponse
    {
        $this->authorizeCouponAction(request(), 'coupons.update');

        $coupon->update([
            'is_active' => ! $coupon->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái coupon thành công.',
            'data' => $coupon->fresh(),
        ]);
    }

    public function apply(ApplyCouponRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $subtotal = (float) $validated['subtotal'];
        $code = strtoupper(trim((string) $validated['code']));

        $result = DB::transaction(function () use ($code, $subtotal) {
            $coupon = Coupon::query()
                ->where('code', $code)
                ->lockForUpdate()
                ->first();

            if (! $coupon) {
                throw ValidationException::withMessages([
                    'code' => ['Mã giảm giá không tồn tại.'],
                ]);
            }

            if (! $coupon->is_active) {
                throw ValidationException::withMessages([
                    'code' => ['Mã giảm giá hiện không khả dụng.'],
                ]);
            }

            if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
                throw ValidationException::withMessages([
                    'code' => ['Mã giảm giá chưa đến thời gian áp dụng.'],
                ]);
            }

            if ($coupon->ends_at && $coupon->ends_at->isPast()) {
                throw ValidationException::withMessages([
                    'code' => ['Mã giảm giá đã hết hạn.'],
                ]);
            }

            if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
                throw ValidationException::withMessages([
                    'code' => ['Mã giảm giá đã hết lượt sử dụng.'],
                ]);
            }

            if ($subtotal < (float) $coupon->min_order_amount) {
                throw ValidationException::withMessages([
                    'code' => ['Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã.'],
                ]);
            }

            $discount = $this->calculateDiscount($coupon, $subtotal);

            return [
                'coupon' => $coupon,
                'discount_amount' => $discount,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng coupon thành công.',
            'data' => [
                'coupon' => $result['coupon'],
                'discount_amount' => $result['discount_amount'],
            ],
        ]);
    }

    private function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return max(min($discount, $subtotal), 0);
    }

    private function authorizeCouponAction(Request $request, string $permission): void
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, 'Forbidden.');
        }
    }
}
