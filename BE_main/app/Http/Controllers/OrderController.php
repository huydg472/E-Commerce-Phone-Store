<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\Services\OrderInventoryService;
use App\Services\OrderPricingService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $shippingMethod = (string) ($data['shipping_method'] ?? 'standard');
        $shippingFee = $this->pricingService->resolveShippingFee($shippingMethod);
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
            $data['shipping_fee'] = $shippingFee;
            unset($data['payment_method']);
            unset($data['shipping_method']);

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

        if (!$this->isAllowedStatusTransition($previousStatus, $nextStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Không cho phép chuyển sang trạng thái này.',
            ], 422);
        }

        $wasCompleted = $order->completed_at !== null;

        DB::transaction(function () use ($order, $data, $previousStatus, $nextStatus, $wasCompleted) {
            $order->update($data);

            if ($nextStatus === 'cancelled' && !$wasCompleted) {
                $this->inventoryService->releaseReservedStockForOrder($order);
            }

            if ($previousStatus === 'cancelled' && $nextStatus === 'pending' && !$wasCompleted) {
                $this->inventoryService->reserveStockForOrder($order);
            }

            if ($nextStatus === 'completed' && !$wasCompleted) {
                $this->inventoryService->commitReservedStockForOrder($order);
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
    private function isAllowedStatusTransition(string $currentStatus, string $nextStatus): bool
    {
        if ($currentStatus === $nextStatus) {
            return true;
        }

        $steps = ['pending', 'confirmed', 'processing', 'shipping', 'completed'];

        if ($nextStatus === 'cancelled') {
            return $currentStatus !== 'completed';
        }

        if ($currentStatus === 'cancelled') {
            return $nextStatus === 'pending';
        }

        $currentIndex = array_search($currentStatus, $steps, true);
        $nextIndex = array_search($nextStatus, $steps, true);

        if ($currentIndex === false || $nextIndex === false) {
            return false;
        }

        return abs($nextIndex - $currentIndex) === 1;
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
                if ($order->completed_at) {
                    $this->inventoryService->releaseCommittedStockForOrder($order);
                } elseif (in_array($order->order_status, ['pending', 'confirmed', 'processing', 'shipping'], true)) {
                    $this->inventoryService->releaseReservedStockForOrder($order);
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

}

