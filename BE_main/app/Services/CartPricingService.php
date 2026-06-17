<?php

namespace App\Services;

use App\Models\Cart;

class CartPricingService
{
    public function applyPricing(Cart $cart): Cart
    {
        $cart->loadMissing(['items.productVariant.product']);

        $subtotal = 0.0;

        foreach ($cart->items as $item) {
            $variant = $item->productVariant;
            $price = (float)($variant?->sale_price ?? $variant?->price ?? 0);
            $itemSubtotal = $price * (int)$item->quantity;

            $item->setAttribute('price', $price);
            $item->setAttribute('subtotal', $itemSubtotal);

            $subtotal += $itemSubtotal;
        }

        $cart->setAttribute('subtotal', $subtotal);
        $cart->setAttribute('total_amount', $subtotal);

        return $cart;
    }
}
