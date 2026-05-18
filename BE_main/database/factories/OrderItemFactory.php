<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $order = Order::query()->inRandomOrder()->first()
            ?? Order::factory()->create();

        $variant = ProductVariant::query()->inRandomOrder()->first()
            ?? ProductVariant::factory()->create();

        $product = Product::find($variant->product_id);
        $unitPrice = $variant->sale_price ?? $variant->price;
        $quantity = fake()->numberBetween(1, 3);

        return [
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product?->name ?? 'Sản phẩm mẫu',
            'variant_name' => $variant->color . ' - ' . $variant->storage . ' - ' . $variant->ram,
            'sku' => $variant->sku,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $unitPrice * $quantity,
        ];
    }
}
