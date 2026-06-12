<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\Services\OrderInventoryService;
use App\Services\OrderPricingService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderInventoryService $inventoryService,
        private readonly OrderPricingService $pricingService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Order::query()
            ->with(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user', 'coupon'])
            ->latest();

        if (!$request->user()->isAdminOrStaff()) {
            $query->where('user_id', $request->user()->id);
        }

        $order = $query->get();

        return response()->json([
            'success' => true,
            'message' => "Lấy dữ liệu thành công",
            'data' => $order
        ], 200);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $items = $data['items'] ?? [];
        unset($data['items']);

        if (!empty($data['shipping_address_id']) && !$request->user()->isAdminOrStaff()) {
            $shippingAddress = ShippingAddress::find($data['shipping_address_id']);

            if (!$shippingAddress || $shippingAddress->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.',
                ], 403);
            }
        }

        $shippingFee = (float) ($data['shipping_fee'] ?? 0);
        $couponCode = strtoupper(trim((string) ($data['coupon_code'] ?? '')));
        $paymentMethod = $data['payment_method'] ?? 'cod';

        $order = DB::transaction(function () use ($data, $items, $paymentMethod, $shippingFee, $couponCode) {
            $inventory = $this->inventoryService->normalizeOrderItems($items);
            $normalizedItems = $inventory['items'];
            $subtotal = (float) $inventory['subtotal'];
            $couponResult = $this->pricingService->resolveCouponDiscount($couponCode, $subtotal);
            $discountAmount = $couponResult['discount_amount'];
            $coupon = $couponResult['coupon'];

            $data['order_code'] = $this->pricingService->generateOrderCode();
            $data['subtotal'] = $subtotal;
            $data['total_amount'] = max($subtotal + $shippingFee - $discountAmount, 0);
            $data['coupon_id'] = $coupon?->id;
            $data['coupon_code'] = $coupon?->code;
            $data['discount_amount'] = $discountAmount;
            unset($data['payment_method']);

            $order = Order::create($data);
            $order->orderItems()->createMany($normalizedItems);
            $this->inventoryService->reserveStockForOrder($order);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'amount' => $order->total_amount,
                'transaction_code' => null,
                'paid_at' => null,
                'note' => $data['note'] ?? null,
            ]);

            return $order;
        });

        $order->load(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user', 'coupon']);

        return response()->json([
            'success' => true,
            'message' => "Tạo dữ liệu thành công",
            'data' => $order
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $order->load(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user', 'coupon']);

        if (!$request->user()->isAdminOrStaff() && $order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => "Lấy chi tiết dữ liệu thành công",
            'data' => $order
        ], 200);
    }

    public function mockPayment(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrStaff() && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        if ($order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã bị huỷ.',
            ], 409);
        }

        $data = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_code' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'payment_status' => 'paid',
            ]);

            $payment = $order->payment()->first();

            if ($payment) {
                $payment->update([
                    'payment_method' => $data['payment_method'] ?? $payment->payment_method,
                    'payment_status' => 'paid',
                    'transaction_code' => $data['transaction_code'] ?? $payment->transaction_code ?? ('MOCK-' . now()->format('YmdHis')),
                    'paid_at' => $payment->paid_at ?? now(),
                ]);
            }
        });

        $order->load(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user', 'coupon']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thanh toán thành công',
            'data' => $order,
        ], 200);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrStaff() && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $order->loadMissing('payment');

        if ($order->order_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể huỷ đơn khi đang chờ xác nhận.',
            ], 422);
        }

        $paymentMethod = strtolower((string) $order->payment?->payment_method);

        if ($paymentMethod !== 'cod') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ đơn thanh toán COD mới có thể tự huỷ.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $this->inventoryService->releaseReservedStockForOrder($order);

            $order->update([
                'order_status' => 'cancelled',
                'payment_status' => 'failed',
                'cancelled_at' => now(),
            ]);

            $payment = $order->payment()->first();

            if ($payment) {
                $payment->update([
                    'payment_status' => 'cancelled',
                ]);
            }
        });

        $order->load(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user', 'coupon']);

        return response()->json([
            'success' => true,
            'message' => 'Huỷ đơn thành công.',
            'data' => $order,
        ], 200);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        if (!$request->user()->isAdminOrStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $data = $request->validated();
        $previousStatus = $order->order_status;
        $nextStatus = $data['order_status'] ?? $previousStatus;

        DB::transaction(function () use ($order, $data, $previousStatus, $nextStatus) {
            $order->update($data);

            if ($previousStatus === 'pending' && $nextStatus === 'cancelled') {
                $this->inventoryService->releaseReservedStockForOrder($order);
            }

            if ($previousStatus === 'cancelled' && $nextStatus !== 'cancelled') {
                $this->inventoryService->reserveStockForOrder($order);
            }

            if ($previousStatus === 'pending' && $nextStatus !== 'pending' && $nextStatus !== 'cancelled') {
                $this->inventoryService->commitReservedStockForOrder($order);
            }

            if ($previousStatus !== 'pending' && $previousStatus !== 'cancelled' && $nextStatus === 'cancelled') {
                $this->inventoryService->releaseCommittedStockForOrder($order);
            }

            if ($nextStatus === 'completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'completed_at' => $order->completed_at ?? now(),
                ]);

                $payment = $order->payment()->first();

                if ($payment) {
                    $payment->update([
                        'payment_status' => 'paid',
                        'paid_at' => $payment->paid_at ?? now(),
                    ]);
                }
            }
        });

        $order->load(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user']);

        return response()->json([
            'success' => true,
            'message' => "Cập nhật dữ liệu thành công",
            'data' => $order
        ], 200);
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        if (!$request->user()->isAdminOrStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        try {
            DB::transaction(function () use ($order) {
                if ($order->order_status === 'pending') {
                    $this->inventoryService->releaseReservedStockForOrder($order);
                }

                if (in_array($order->order_status, ['confirmed', 'processing', 'shipping', 'completed'], true)) {
                    $this->inventoryService->releaseCommittedStockForOrder($order);
                }

                $order->delete();
            });

            return response()->json([
                'success' => true,
                'message' => "Xoá dữ liệu thành công",
                'data' => $order
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Xoá dữ liệu thất bại",
            ], 409);
        }
    }

    private function generateOrderCode(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = 'ORD-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

            if (!Order::where('order_code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'ORD-' . now()->format('YmdHis') . '-' . random_int(10000, 99999);
    }

    private function resolveCouponDiscount(string $couponCode, float $subtotal): array
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

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Mã giảm giá không tồn tại.'],
            ]);
        }

        if (! $coupon->is_active) {
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

        if ($subtotal < (float) $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã.'],
            ]);
        }

        $discountAmount = $this->calculateCouponDiscount($coupon, $subtotal);

        return [
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
        ];
    }

    private function calculateCouponDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return max(min($discount, $subtotal), 0);
    }
}
