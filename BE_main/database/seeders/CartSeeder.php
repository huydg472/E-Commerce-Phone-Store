<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $customerRoleId = Role::where('name', 'customer')->value('id');
        $variants = ProductVariant::query()
            ->where('status', 'active')
            ->take(6)
            ->get();

        $carts = User::query()
            ->when($customerRoleId, fn($query) => $query->where('role_id', $customerRoleId))
            ->limit(5)
            ->get()
            ->map(function (User $user) {
                return Cart::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'status' => 'active',
                    ]
                );
            });

        if ($variants->isEmpty()) {
            return;
        }

        $carts->filter()->each(function (Cart $cart) use ($variants) {
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
