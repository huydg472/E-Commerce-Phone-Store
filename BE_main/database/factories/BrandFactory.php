<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'logo_url' => fake()->imageUrl(300, 300, 'technics', true),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
