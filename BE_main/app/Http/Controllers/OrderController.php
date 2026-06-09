<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingAddress;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()
            ->with(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user'])
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

        $subtotal = collect($items)->sum(fn ($item) => (float) ($item['total_price'] ?? 0));
        $shippingFee = (float) ($data['shipping_fee'] ?? 0);
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'cod';

        $data['subtotal'] = $subtotal;
        $data['total_amount'] = max($subtotal + $shippingFee - $discountAmount, 0);

        $order = DB::transaction(function () use ($data, $items, $paymentMethod) {
            $data['order_code'] = $this->generateOrderCode();
            unset($data['payment_method']);
            $order = Order::create($data);

            if ($items !== []) {
                $order->orderItems()->createMany(array_map(function (array $item) {
                    return [
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'variant_name' => $item['variant_name'],
                        'sku' => $item['sku'] ?? null,
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'total_price' => $item['total_price'],
                    ];
                }, $items));
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

        $order->load(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user']);

        return response()->json([
            'success' => true,
            'message' => "Tạo dữ liệu thành công",
            'data' => $order
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $order->load(['orderItems.productVariant.product', 'payment', 'shippingAddress', 'user']);

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

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        if (!$request->user()->isAdminOrStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $data = $request->validated();

        DB::transaction(function () use ($order, $data) {
            $order->update($data);

            if (($data['order_status'] ?? null) === 'completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'completed_at' => $order->completed_at ?? now(),
                ]);

                $order->payment()?->update([
                    'payment_status' => 'paid',
                    'paid_at' => $order->payment?->paid_at ?? now(),
                ]);
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
            $order->delete();

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
}
