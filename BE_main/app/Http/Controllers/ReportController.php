<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function resolveDashboardPeriod(Request $request): array
    {
        $period = strtolower((string) $request->query('period', 'month'));

        if (!in_array($period, ['7d', '30d', 'month'], true)) {
            $period = 'month';
        }

        $end = now();
        $start = match ($period) {
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            default => now()->startOfMonth(),
        };

        $periodLabel = match ($period) {
            '7d' => '7 ngày gần đây',
            '30d' => '30 ngày gần đây',
            default => 'Tháng này',
        };

        return [$period, $periodLabel, $start, $end];
    }

    private function resolveOrderDate(Order $order): ?Carbon
    {
        $source = $order->ordered_at ?? $order->completed_at ?? $order->created_at;

        if (!$source) {
            return null;
        }

        try {
            return Carbon::parse($source);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatRevenueLabel(Carbon $date): string
    {
        return $date->format('d/m');
    }

    private function buildRevenueSeries(Collection $orders, Carbon $start, Carbon $end): array
    {
        $bucketCount = 7;
        $span = max($end->timestamp - $start->timestamp, 1);
        $bucketSpan = $span / $bucketCount;
        $buckets = [];

        for ($index = 0; $index < $bucketCount; $index++) {
            $bucketStart = $start->copy()->addSeconds((int) floor($bucketSpan * $index));

            $buckets[$index] = [
                'day' => $this->formatRevenueLabel($bucketStart),
                'label' => $this->formatRevenueLabel($bucketStart),
                'percent' => 0,
                'amount' => 0,
                'total' => 0,
            ];
        }

        foreach ($orders as $order) {
            $date = $this->resolveOrderDate($order);

            if (!$date || $date->lt($start) || $date->gt($end)) {
                continue;
            }

            $bucketIndex = min(
                $bucketCount - 1,
                max(0, (int) floor(($date->timestamp - $start->timestamp) / $bucketSpan))
            );

            $buckets[$bucketIndex]['total'] += (float) ($order->total_amount ?? 0);
        }

        $maxTotal = max(
            array_reduce($buckets, static fn (float $carry, array $bucket) => max($carry, (float) $bucket['total']), 0.0),
            1
        );

        return array_map(static function (array $bucket) use ($maxTotal): array {
            $bucket['percent'] = max(16, (int) round(($bucket['total'] / $maxTotal) * 100));
            $bucket['amount'] = $bucket['total'];

            return $bucket;
        }, array_values($buckets));
    }

    private function buildTopProducts(Collection $orders): array
    {
        $productMap = [];

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $variant = $item->productVariant;
                $product = $variant?->product;
                $key = $variant?->id ?? $item->product_variant_id ?? $item->product_name . '-' . $item->variant_name;

                if (!isset($productMap[$key])) {
                    $productMap[$key] = [
                        'id' => $key,
                        'name' => $product?->name ?? $item->product_name ?? 'Sản phẩm',
                        'variant' => $item->variant_name ?? $variant?->name ?? 'Biến thể',
                        'sold' => 0,
                        'revenue' => 0,
                    ];
                }

                $productMap[$key]['sold'] += (int) ($item->quantity ?? 0);
                $productMap[$key]['revenue'] += (float) ($item->total_price ?? 0);
            }
        }

        $items = array_values($productMap);
        usort($items, static fn (array $left, array $right) => $right['revenue'] <=> $left['revenue']);

        return array_slice($items, 0, 4);
    }

    private function buildRecentOrders(Collection $orders): array
    {
        return $orders
            ->sortByDesc(fn (Order $order) => $this->resolveOrderDate($order)?->timestamp ?? 0)
            ->take(5)
            ->values()
            ->map(function (Order $order) {
                $firstItem = $order->orderItems->first();
                $variant = $firstItem?->productVariant;
                $productName = $firstItem?->product_name ?? $variant?->product?->name ?? 'Sản phẩm';

                return [
                    'id' => $order->id,
                    'code' => $order->order_code ?? ('#' . $order->id),
                    'customer' => $order->receiver_name ?? $order->user?->name ?? 'Khách hàng',
                    'product' => $productName,
                    'total' => (float) ($order->total_amount ?? 0),
                    'status' => $order->order_status ?? 'pending',
                    'date' => $this->resolveOrderDate($order)?->toIso8601String(),
                ];
            })
            ->all();
    }

    public function dashboard(Request $request): JsonResponse
    {
        [$period, $periodLabel, $start, $end] = $this->resolveDashboardPeriod($request);

        $orders = Order::query()
            ->with(['orderItems.productVariant.product', 'payment', 'user'])
            ->latest()
            ->get();

        $filteredOrders = $orders
            ->filter(function (Order $order) use ($start, $end) {
                $date = $this->resolveOrderDate($order);

                return $date && $date->betweenIncluded($start, $end);
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'period_label' => $periodLabel,
                'revenue_series' => $this->buildRevenueSeries($filteredOrders, $start, $end),
                'top_products' => $this->buildTopProducts($filteredOrders),
                'recent_orders' => $this->buildRecentOrders($filteredOrders),
            ],
        ]);
    }

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
