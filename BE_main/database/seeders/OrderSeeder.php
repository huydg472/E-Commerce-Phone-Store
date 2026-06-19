<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\StockLog;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customerRoleId = Role::where('name', 'customer')->value('id');
        $variants = ProductVariant::query()
            ->where('status', 'active')
            ->take(8)
            ->get();

        $users = User::query()
            ->when($customerRoleId, fn($query) => $query->where('role_id', $customerRoleId))
            ->limit(5)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $orders = [];

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

            $order = Order::updateOrCreate(
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

            $orders[] = $order->fresh();
        }

        if ($variants->isNotEmpty()) {
            foreach ($orders as $index => $order) {
                OrderItem::where('order_id', $order->id)->delete();

                $selectedVariants = $variants->random(min(2, $variants->count()));
                $subtotal = 0;

                foreach ($selectedVariants as $variant) {
                    $product = Product::find($variant->product_id);
                    $quantity = fake()->numberBetween(1, 2);
                    $unitPrice = $variant->sale_price ?? $variant->price;
                    $totalPrice = $unitPrice * $quantity;
                    $subtotal += $totalPrice;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'product_name' => $product?->name ?? 'Sản phẩm mẫu',
                        'variant_name' => $variant->color . ' - ' . $variant->storage . ' - ' . $variant->ram,
                        'sku' => $variant->sku,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'total_price' => $totalPrice,
                    ]);
                }

                $shippingFee = 30000;
                $discountAmount = $index % 2 === 0 ? min(100000, $subtotal + $shippingFee) : 0;

                $order->update([
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $subtotal + $shippingFee - $discountAmount,
                ]);
            }
        }

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

        StockLog::query()->delete();

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $adminUserId = User::query()
            ->when($adminRoleId, fn($query) => $query->where('role_id', $adminRoleId))
            ->value('id');

        ProductVariant::query()->get()->each(function (ProductVariant $variant) use ($adminUserId) {
            StockLog::create([
                'product_variant_id' => $variant->id,
                'user_id' => $adminUserId,
                'order_id' => null,
                'type' => 'import',
                'quantity_before' => 0,
                'quantity_change' => $variant->quantity,
                'quantity_after' => $variant->quantity,
                'note' => 'Nhập kho ban đầu từ seeder',
            ]);
        });

        $completedOrderIds = Order::query()
            ->where('order_status', 'completed')
            ->pluck('id');

        OrderItem::query()
            ->whereIn('order_id', $completedOrderIds)
            ->whereNotNull('product_variant_id')
            ->get()
            ->each(function (OrderItem $item) use ($adminUserId) {
                $variant = ProductVariant::find($item->product_variant_id);

                if (!$variant) {
                    return;
                }

                StockLog::create([
                    'product_variant_id' => $variant->id,
                    'user_id' => $adminUserId,
                    'order_id' => $item->order_id,
                    'type' => 'sale',
                    'quantity_before' => $variant->quantity + $item->quantity,
                    'quantity_change' => -$item->quantity,
                    'quantity_after' => $variant->quantity,
                    'note' => 'Xuất kho theo đơn hàng mẫu',
                ]);
            });
    }
}
