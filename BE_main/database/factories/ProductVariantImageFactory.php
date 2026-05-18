<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariantImageFactory extends Factory
{
    public function definition(): array
    {
        $variant = ProductVariant::query()->inRandomOrder()->first()
            ?? ProductVariant::factory()->create();

        return [
            'product_variant_id' => $variant->id,
            'image_url' => 'https://placehold.co/600x600?text=' . rawurlencode($variant->sku . '-' . Str::random(4)),
            'alt_text' => 'Ảnh sản phẩm ' . $variant->sku,
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
