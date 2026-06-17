<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
            'status' => 'active',
        ];
    }

    public function accessoryForBrand(Brand $brand, Category $category, string $name, array $overrides = []): static
    {
        return $this->state(function () use ($brand, $category, $name, $overrides) {
            return array_merge([
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'thumbnail_url' => 'https://placehold.co/600x600?text=' . rawurlencode($name),
                'short_description' => $name . ' phu hop cho nhu cau sac, bao ve va ket noi hang ngay.',
                'description' => $name . ' la san pham phu kien mau, phuc vu nhu cau su dung pho bien tren dien thoai.',
                'status' => 'active',
            ], $overrides);
        });
    }
}
