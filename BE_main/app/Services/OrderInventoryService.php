<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockLog;
use Illuminate\Validation\ValidationException;

class OrderInventoryService
{
    public function normalizeOrderItems(array $items): array
    {
        $groupedItems = collect($items)
            ->filter(fn ($item) => is_array($item))
            ->groupBy(fn ($item) => (int) ($item['product_variant_id'] ?? 0))
            ->filter(fn ($group, $variantId) => (int) $variantId > 0);

        if ($groupedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Vui lòng thêm ít nhất một sản phẩm hợp lệ.'],
            ]);
        }

        $variantIds = $groupedItems->keys()->map(fn ($value) => (int) $value)->values()->all();

        $variants = ProductVariant::query()
            ->with(['product.brand', 'product.category'])
            ->whereIn('id', $variantIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $normalizedItems = [];
        $subtotal = 0.0;

        foreach ($groupedItems as $variantId => $group) {
            $variant = $variants->get((int) $variantId);

            if (!$variant) {
                throw ValidationException::withMessages([
                    "items.$variantId.product_variant_id" => ['Biến thể sản phẩm không tồn tại.'],
                ]);
            }

            if (
                $variant->status !== 'active' ||
                $variant->product?->status !== 'active' ||
                $variant->product?->brand?->status !== 'active' ||
                $variant->product?->category?->status !== 'active'
            ) {
                throw ValidationException::withMessages([
                    "items.$variantId.product_variant_id" => ['Sản phẩm này hiện không còn bán.'],
                ]);
            }

            $quantity = (int) $group->sum(fn ($item) => max((int) ($item['quantity'] ?? 0), 0));

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    "items.$variantId.quantity" => ['Số lượng phải lớn hơn 0.'],
                ]);
            }

            if ($this->availableQuantity($variant) < $quantity) {
                throw ValidationException::withMessages([
                    "items.$variantId.quantity" => ['Số lượng tồn kho không đủ.'],
                ]);
            }

            $unitPrice = (float) ($variant->sale_price ?? $variant->price ?? 0);
            $itemTotal = $unitPrice * $quantity;

            $normalizedItems[] = [
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product?->name ?? 'Sản phẩm',
                'variant_name' => $this->formatVariantName($variant),
                'sku' => $variant->sku,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $itemTotal,
            ];

            $subtotal += $itemTotal;
        }

        return [
            'items' => $normalizedItems,
            'subtotal' => $subtotal,
        ];
    }

    public function reserveStockForOrder(Order $order): void
    {
        $this->adjustReservedStockForOrder($order, 1);
    }

    public function releaseReservedStockForOrder(Order $order): void
    {
        $this->adjustReservedStockForOrder($order, -1);
    }

    public function commitReservedStockForOrder(Order $order): void
    {
        $orderItems = $this->loadOrderItems($order);
        if ($orderItems->isEmpty()) {
            return;
        }

        $variants = $this->loadVariantsForOrderItems($orderItems);

        foreach ($orderItems as $orderItem) {
            $variant = $variants->get((int) $orderItem->product_variant_id);

            if (!$variant) {
                throw ValidationException::withMessages([
                    'order' => ['Không tìm thấy biến thể sản phẩm của đơn hàng.'],
                ]);
            }

            $quantity = (int) $orderItem->quantity;
            $reservedBefore = (int) $variant->reserved_quantity;
            $stockBefore = (int) $variant->quantity;
            $hasReservedStock = $reservedBefore >= $quantity;

            if ($stockBefore < $quantity) {
                throw ValidationException::withMessages([
                    'order' => ['Số lượng tồn kho không đủ để xác nhận đơn hàng.'],
                ]);
            }

            if (!$hasReservedStock && $this->availableQuantity($variant) < $quantity) {
                throw ValidationException::withMessages([
                    'order' => ['Số lượng tồn kho khả dụng không đủ để xác nhận đơn hàng.'],
                ]);
            }

            $variant->update([
                'quantity' => $stockBefore - $quantity,
                'reserved_quantity' => $hasReservedStock ? $reservedBefore - $quantity : $reservedBefore,
            ]);

            StockLog::create([
                'product_variant_id' => $variant->id,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'type' => 'sale',
                'quantity_before' => $stockBefore,
                'quantity_change' => -$quantity,
                'quantity_after' => $stockBefore - $quantity,
                'note' => 'Trừ kho khi xác nhận đơn hàng #' . $order->order_code,
                'created_at' => now(),
            ]);
        }
    }

    public function releaseCommittedStockForOrder(Order $order): void
    {
        $orderItems = $this->loadOrderItems($order);
        if ($orderItems->isEmpty()) {
            return;
        }

        $variants = $this->loadVariantsForOrderItems($orderItems);

        foreach ($orderItems as $orderItem) {
            $variant = $variants->get((int) $orderItem->product_variant_id);

            if (!$variant) {
                throw ValidationException::withMessages([
                    'order' => ['Không tìm thấy biến thể sản phẩm của đơn hàng.'],
                ]);
            }

            $quantity = (int) $orderItem->quantity;
            $stockBefore = (int) $variant->quantity;

            $variant->update([
                'quantity' => $stockBefore + $quantity,
            ]);

            StockLog::create([
                'product_variant_id' => $variant->id,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'type' => 'cancel_order',
                'quantity_before' => $stockBefore,
                'quantity_change' => $quantity,
                'quantity_after' => $stockBefore + $quantity,
                'note' => 'Hoàn kho khi huỷ đơn hàng #' . $order->order_code,
                'created_at' => now(),
            ]);
        }
    }

    private function adjustReservedStockForOrder(Order $order, int $direction): void
    {
        $orderItems = $this->loadOrderItems($order);
        if ($orderItems->isEmpty()) {
            return;
        }

        $variants = $this->loadVariantsForOrderItems($orderItems);

        foreach ($orderItems as $orderItem) {
            $variant = $variants->get((int) $orderItem->product_variant_id);

            if (!$variant) {
                throw ValidationException::withMessages([
                    'order' => ['Không tìm thấy biến thể sản phẩm của đơn hàng.'],
                ]);
            }

            $quantity = (int) $orderItem->quantity;
            $reservedBefore = (int) $variant->reserved_quantity;
            $reservedAfter = $reservedBefore + ($direction * $quantity);

            if ($reservedAfter < 0) {
                throw ValidationException::withMessages([
                    'order' => ['Số lượng giữ chỗ không hợp lệ.'],
                ]);
            }

            if ($direction > 0 && $this->availableQuantity($variant) < $quantity) {
                throw ValidationException::withMessages([
                    'order' => ['Số lượng tồn kho không đủ để giữ chỗ.'],
                ]);
            }

            $variant->update([
                'reserved_quantity' => $reservedAfter,
            ]);
        }
    }

    private function loadOrderItems(Order $order)
    {
        return $order->orderItems()
            ->whereNotNull('product_variant_id')
            ->get();
    }

    private function loadVariantsForOrderItems($orderItems)
    {
        $variantIds = $orderItems->pluck('product_variant_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function availableQuantity(ProductVariant $variant): int
    {
        return max((int) $variant->quantity - (int) $variant->reserved_quantity, 0);
    }

    private function formatVariantName(ProductVariant $variant): string
    {
        $parts = array_filter([
            $variant->color,
            $variant->storage,
            $variant->ram,
        ]);

        if ($parts !== []) {
            return implode(' - ', $parts);
        }

        return $variant->sku ?: 'Phiên bản mặc định';
    }
}
