<?php

namespace Database\Factories;

use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsPostFactory extends Factory
{
    protected $model = NewsPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(8);
        $status = fake()->randomElement(['draft', 'published']);

        return [
            'news_category_id' => NewsCategory::query()->inRandomOrder()->value('id')
                ?? NewsCategory::factory()->create()->id,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 9999),
            'excerpt' => fake()->sentence(18),
            'content' => fake()->paragraphs(5, true),
            'featured_image_url' => 'https://placehold.co/1200x700?text=' . rawurlencode($title),
            'status' => $status,
            'is_featured' => fake()->boolean(25),
            'reading_minutes' => fake()->numberBetween(3, 9),
            'views_count' => fake()->numberBetween(0, 5000),
            'published_at' => $status === 'published' ? fake()->dateTimeBetween('-120 days', 'now') : null,
        ];
    }
}
