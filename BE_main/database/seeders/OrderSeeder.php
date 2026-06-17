<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customerRoleId = Role::where('name', 'customer')->value('id');

        $users = User::query()
            ->when($customerRoleId, fn($query) => $query->where('role_id', $customerRoleId))
            ->limit(5)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $index => $user) {
            $address = ShippingAddress::query()
                ->where('user_id', $user->id)
                ->where('is_default', true)
                ->first()
                ?? ShippingAddress::factory()->create([
                    'user_id' => $user->id,
                    'receiver_name' => $user->name,
                    'receiver_phone' => $user->phone,
                    'is_default' => true,
                ]);

            $isCompleted = $index % 3 === 0;
            $orderCode = 'ORD-DEMO-' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT);

            Order::updateOrCreate(
                ['order_code' => $orderCode],
                [
                    'user_id' => $user->id,
                    'shipping_address_id' => $address->id,
                    'order_code' => $orderCode,
                    'receiver_name' => $address->receiver_name,
                    'receiver_phone' => $address->receiver_phone,
                    'shipping_address_text' => $address->address_detail . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->province,
                    'subtotal' => 0,
                    'shipping_fee' => 30000,
                    'discount_amount' => 0,
                    'total_amount' => 30000,
                    'payment_status' => $isCompleted ? 'paid' : 'unpaid',
                    'order_status' => $isCompleted ? 'completed' : 'pending',
                    'note' => 'Đơn hàng mẫu',
                    'ordered_at' => now()->subDays(5 - $index),
                    'cancelled_at' => null,
                    'completed_at' => $isCompleted ? now()->subDays(1) : null,
                ]
            );
        }
    }
}
