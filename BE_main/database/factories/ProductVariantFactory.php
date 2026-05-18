<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 1;

        $productId = Product::query()->inRandomOrder()->value('id')
            ?? Product::factory()->create()->id;

        $price = fake()->numberBetween(4_000_000, 30_000_000);
        $salePrice = fake()->boolean(35) ? fake()->numberBetween((int) ($price * 0.85), $price) : null;
        $quantity = fake()->numberBetween(0, 80);

        return [
            'product_id' => $productId,
            'color' => fake()->randomElement(['Đen', 'Trắng', 'Xanh', 'Tím', 'Vàng']) . ' ' . $counter,
            'storage' => fake()->randomElement(['64GB', '128GB', '256GB', '512GB']),
            'ram' => fake()->randomElement(['4GB', '6GB', '8GB', '12GB']),
            'sku' => 'SKU-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)) . '-' . $counter++,
            'import_price' => fake()->numberBetween(3_000_000, max(3_000_000, (int) ($price * 0.8))),
            'price' => $price,
            'sale_price' => $salePrice,
            'quantity' => $quantity,
            'status' => $quantity > 0 ? 'active' : 'out_of_stock',
            'description' => fake()->sentence(),
        ];
    }
}
