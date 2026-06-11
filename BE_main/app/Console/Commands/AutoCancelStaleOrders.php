<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderInventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCancelStaleOrders extends Command
{
    protected $signature = 'orders:auto-cancel-stale';

    protected $description = 'Auto cancel stale unpaid orders after 12 hours and release reserved stock.';

    public function handle(OrderInventoryService $inventoryService): int
    {
        $threshold = now()->subHours(12);

        $orders = Order::query()
            ->with('payment')
            ->where('order_status', 'pending')
            ->whereIn('payment_status', ['unpaid', 'pending', 'failed'])
            ->where('ordered_at', '<=', $threshold)
            ->orderBy('id')
            ->get();

        $cancelled = 0;

        foreach ($orders as $order) {
            DB::transaction(function () use ($order, $inventoryService, &$cancelled) {
                $freshOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->with('payment')
                    ->first();

                if (!$freshOrder) {
                    return;
                }

                if ($freshOrder->order_status !== 'pending') {
                    return;
                }

                if ($freshOrder->payment_status === 'paid') {
                    return;
                }

                $inventoryService->releaseReservedStockForOrder($freshOrder);

                $freshOrder->update([
                    'order_status' => 'cancelled',
                    'payment_status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                if ($freshOrder->payment) {
                    $freshOrder->payment->update([
                        'payment_status' => 'cancelled',
                    ]);
                }

                $cancelled += 1;
            });
        }

        $this->info("Auto-cancelled {$cancelled} stale order(s).");

        return self::SUCCESS;
    }
}
