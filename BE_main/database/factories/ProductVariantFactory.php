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
            'is_featured' => false,
            'description' => fake()->sentence(),
        ];
    }

    public function accessoryForProduct(Product $product, array $overrides = []): static
    {
        return $this->state(function () use ($product, $overrides) {
            $name = strtolower((string) $product->name);
            $storage = $overrides['storage'] ?? $this->guessAccessoryStorage($name);
            $color = $overrides['color'] ?? $this->guessAccessoryColor($name);
            $price = $overrides['price'] ?? $this->guessAccessoryPrice($name);
            $quantity = $overrides['quantity'] ?? fake()->numberBetween(12, 120);
            $salePrice = array_key_exists('sale_price', $overrides)
                ? $overrides['sale_price']
                : (fake()->boolean(35) ? max(0, $price - fake()->numberBetween(10_000, (int) max(30_000, $price * 0.18))) : null);

            return array_merge([
                'product_id' => $product->id,
                'color' => $color,
                'storage' => $storage,
                'ram' => 'N/A',
                'sku' => 'ACC-' . Str::upper(Str::slug($product->slug)) . '-' . Str::upper(Str::random(6)),
                'import_price' => (int) round($price * 0.72, -2),
                'price' => $price,
                'sale_price' => $salePrice && $salePrice <= $price ? $salePrice : null,
                'quantity' => $quantity,
                'status' => 'active',
                'is_featured' => (bool) ($overrides['is_featured'] ?? false),
                'description' => $product->name . ' ' . $storage,
            ], $overrides);
        });
    }

    private function guessAccessoryStorage(string $name): string
    {
        if (str_contains($name, 'power bank') || str_contains($name, 'powerbank')) {
            return fake()->randomElement(['5000mAh', '10000mAh', '20000mAh', '30000mAh']);
        }

        if (str_contains($name, 'cable') || str_contains($name, 'usb')) {
            return fake()->randomElement(['0.5m', '1m', '1.5m', '2m', '3m']);
        }

        if (str_contains($name, 'charger') || str_contains($name, 'gan') || str_contains($name, 'wireless')) {
            return fake()->randomElement(['20W', '30W', '45W', '65W', '100W']);
        }

        if (str_contains($name, 'hub') || str_contains($name, 'dock')) {
            return fake()->randomElement(['4-in-1', '5-in-1', '7-in-1', '9-in-1', '11-in-1']);
        }

        if (str_contains($name, 'case') || str_contains($name, 'ốp') || str_contains($name, 'op') || str_contains($name, 'screen protector')) {
            return fake()->randomElement(['1 Pack', '2 Pack']);
        }

        return fake()->randomElement(['1 Pack', '2 Pack']);
    }

    private function guessAccessoryColor(string $name): string
    {
        if (str_contains($name, 'clear') || str_contains($name, 'protector')) {
            return 'Transparent';
        }

        return fake()->randomElement(['Black', 'White', 'Blue', 'Gray', 'Transparent']);
    }

    private function guessAccessoryPrice(string $name): int
    {
        if (str_contains($name, 'power bank') || str_contains($name, 'powerbank')) {
            return fake()->numberBetween(259000, 1299000);
        }

        if (str_contains($name, 'charger') || str_contains($name, 'gan') || str_contains($name, 'wireless')) {
            return fake()->numberBetween(149000, 999000);
        }

        if (str_contains($name, 'cable') || str_contains($name, 'usb')) {
            return fake()->numberBetween(49000, 299000);
        }

        if (str_contains($name, 'hub') || str_contains($name, 'dock')) {
            return fake()->numberBetween(299000, 1499000);
        }

        if (str_contains($name, 'case') || str_contains($name, 'ốp') || str_contains($name, 'op') || str_contains($name, 'screen protector')) {
            return fake()->numberBetween(39000, 249000);
        }

        return fake()->numberBetween(99000, 499000);
    }
}
