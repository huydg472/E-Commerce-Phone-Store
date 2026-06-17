<?php

namespace Database\Factories;

use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsCategoryFactory extends Factory
{
    protected $model = NewsCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Tin công nghệ',
            'Đánh giá',
            'Mẹo hay',
            'Khuyến mãi',
            'So sánh',
            'Xu hướng',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'status' => 'active',
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
