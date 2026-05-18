<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $usedOrderIds = Payment::query()->pluck('order_id');

        $order = Order::query()
            ->whereNotIn('id', $usedOrderIds)
            ->inRandomOrder()
            ->first()
            ?? Order::factory()->create();

        $status = $order->payment_status === 'paid' ? 'paid' : 'pending';

        return [
            'order_id' => $order->id,
            'payment_method' => fake()->randomElement(['cod', 'bank_transfer', 'vnpay', 'momo']),
            'payment_status' => $status,
            'amount' => $order->total_amount,
            'transaction_code' => $status === 'paid' ? 'TXN-' . Str::upper(Str::random(10)) : null,
            'paid_at' => $status === 'paid' ? now() : null,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
