<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductSpecificationFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 1;

        $productId = Product::query()->inRandomOrder()->value('id')
            ?? Product::factory()->create()->id;

        return [
            'product_id' => $productId,
            'spec_name' => 'Thông số ' . $counter++,
            'spec_value' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
