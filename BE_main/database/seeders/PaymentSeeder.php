<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        Order::query()->get()->each(function (Order $order) {
            $isPaid = $order->payment_status === 'paid';
            $method = $isPaid ? fake()->randomElement(['bank_transfer', 'vnpay', 'momo']) : 'cod';

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'order_id' => $order->id,
                    'payment_method' => $method,
                    'payment_status' => $isPaid ? 'paid' : 'pending',
                    'amount' => $order->total_amount,
                    'transaction_code' => $isPaid ? 'TXN-' . Str::upper(Str::random(10)) : null,
                    'paid_at' => $isPaid ? now() : null,
                    'note' => $isPaid ? 'Thanh toán mẫu thành công' : 'Thanh toán mẫu chờ xử lý',
                ]
            );
        });
    }
}
