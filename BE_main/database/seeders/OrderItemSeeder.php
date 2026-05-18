<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::query()
            ->where('status', 'active')
            ->take(8)
            ->get();

        if ($variants->isEmpty()) {
            return;
        }

        Order::query()->get()->each(function (Order $order, int $index) use ($variants) {
            OrderItem::where('order_id', $order->id)->delete();

            $selectedVariants = $variants->random(min(2, $variants->count()));
            $subtotal = 0;

            foreach ($selectedVariants as $variant) {
                $product = Product::find($variant->product_id);
                $quantity = fake()->numberBetween(1, 2);
                $unitPrice = $variant->sale_price ?? $variant->price;
                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product?->name ?? 'Sản phẩm mẫu',
                    'variant_name' => $variant->color . ' - ' . $variant->storage . ' - ' . $variant->ram,
                    'sku' => $variant->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ]);
            }

            $shippingFee = 30000;
            $discountAmount = $index % 2 === 0 ? min(100000, $subtotal + $shippingFee) : 0;

            $order->update([
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $subtotal + $shippingFee - $discountAmount,
            ]);
        });
    }
}
