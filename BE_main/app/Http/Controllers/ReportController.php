<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function revenue(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['orderItems.productVariant.product', 'payment', 'user'])
            ->latest()
            ->get();

        $payments = Payment::query()
            ->with(['order.user', 'order.orderItems.productVariant.product'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
                'payments' => $payments,
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with([
                'brand',
                'category',
                'productVariants.images',
            ])
            ->orderBy('id')
            ->get();

        $orders = Order::query()
            ->with(['orderItems.productVariant.product', 'payment', 'user'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'orders' => $orders,
            ],
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['orderItems.productVariant.product', 'payment', 'user'])
            ->latest()
            ->get();

        $payments = Payment::query()
            ->with(['order.user', 'order.orderItems.productVariant.product'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
                'payments' => $payments,
            ],
        ]);
    }
}
