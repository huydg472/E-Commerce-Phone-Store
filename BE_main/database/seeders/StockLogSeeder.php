<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockLogSeeder extends Seeder
{
    public function run(): void
    {
        StockLog::query()->delete();

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $adminUserId = User::query()
            ->when($adminRoleId, fn ($query) => $query->where('role_id', $adminRoleId))
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

                if (! $variant) {
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
