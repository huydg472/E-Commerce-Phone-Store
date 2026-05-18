<?php

namespace Database\Factories;

use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $user = User::query()->inRandomOrder()->first()
            ?? User::factory()->create();

        $address = ShippingAddress::query()
            ->where('user_id', $user->id)
            ->first()
            ?? ShippingAddress::factory()->create(['user_id' => $user->id, 'is_default' => true]);

        $subtotal = fake()->numberBetween(5_000_000, 30_000_000);
        $shippingFee = fake()->randomElement([0, 30000, 50000]);
        $discountAmount = fake()->randomElement([0, 100000, 200000]);
        $totalAmount = $subtotal + $shippingFee - $discountAmount;

        return [
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'receiver_name' => $address->receiver_name,
            'receiver_phone' => $address->receiver_phone,
            'shipping_address_text' => $address->address_detail . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->province,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'note' => fake()->optional()->sentence(),
            'ordered_at' => now(),
            'cancelled_at' => null,
            'completed_at' => null,
        ];
    }
}
