<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $brandId = Brand::query()->inRandomOrder()->value('id')
            ?? Brand::factory()->create()->id;

        $categoryId = Category::query()->inRandomOrder()->value('id')
            ?? Category::factory()->create()->id;

        $name = fake()->randomElement([
            'Samsung Galaxy A36 5G',
            'Samsung Galaxy A26 5G',
            'iPhone 15',
            'Xiaomi Redmi Note 13',
            'OPPO Reno 11',
            'Vivo V30',
        ]) . ' ' . fake()->unique()->numberBetween(100, 999999);

        return [
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Str::slug($name),
            'thumbnail_url' => 'https://placehold.co/600x600?text=' . rawurlencode($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'is_featured' => fake()->boolean(30),
            'status' => 'active',
        ];
    }
}
