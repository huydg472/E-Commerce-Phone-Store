<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::query()
            ->where('status', 'active')
            ->take(6)
            ->get();

        if ($variants->isEmpty()) {
            return;
        }

        Cart::query()->get()->each(function (Cart $cart) use ($variants) {
            $selectedVariants = $variants->random(min(2, $variants->count()));

            foreach ($selectedVariants as $variant) {
                CartItem::updateOrCreate(
                    [
                        'cart_id' => $cart->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'cart_id' => $cart->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => fake()->numberBetween(1, 2),
                    ]
                );
            }
        });
    }
}
