<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockLogFactory extends Factory
{
    public function definition(): array
    {
        $variantId = ProductVariant::query()->inRandomOrder()->value('id')
            ?? ProductVariant::factory()->create()->id;

        $adminRoleId = Role::where('name', 'admin')->value('id');

        $userId = User::query()
            ->when($adminRoleId, fn ($query) => $query->where('role_id', $adminRoleId))
            ->value('id')
            ?? User::query()->value('id')
            ?? User::factory()->create()->id;

        $type = fake()->randomElement(['import', 'sale', 'cancel_order', 'return', 'adjustment']);
        $quantityBefore = fake()->numberBetween(10, 100);

        $quantityChange = match ($type) {
            'sale' => -fake()->numberBetween(1, min(5, $quantityBefore)),
            'import', 'cancel_order', 'return' => fake()->numberBetween(1, 20),
            default => fake()->randomElement([-1, 1]) * fake()->numberBetween(1, min(10, $quantityBefore)),
        };

        return [
            'product_variant_id' => $variantId,
            'user_id' => $userId,
            'order_id' => null,
            'type' => $type,
            'quantity_before' => $quantityBefore,
            'quantity_change' => $quantityChange,
            'quantity_after' => $quantityBefore + $quantityChange,
            'note' => 'Dữ liệu tồn kho mẫu',
        ];
    }
}
