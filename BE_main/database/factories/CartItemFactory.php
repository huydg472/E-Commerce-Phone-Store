<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    public function definition(): array
    {
        $cart = Cart::query()->inRandomOrder()->first()
            ?? Cart::factory()->create();

        $usedVariantIds = CartItem::query()
            ->where('cart_id', $cart->id)
            ->pluck('product_variant_id');

        $variant = ProductVariant::query()
            ->whereNotIn('id', $usedVariantIds)
            ->inRandomOrder()
            ->first()
            ?? ProductVariant::factory()->create();

        return [
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }
}
