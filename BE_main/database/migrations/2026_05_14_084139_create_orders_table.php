<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('shipping_address_id')
                ->nullable()
                ->constrained('shipping_addresses')
                ->nullOnDelete();

            $table->string('order_code', 50)->unique();
            $table->string('receiver_name', 150);
            $table->string('receiver_phone', 20);
            $table->text('shipping_address_text');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->string('payment_status', 30)->default('unpaid');
            $table->string('order_status', 30)->default('pending');

            $table->text('note')->nullable();
            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_amount_check
            CHECK (
                subtotal >= 0
                AND shipping_fee >= 0
                AND discount_amount >= 0
                AND total_amount >= 0
                AND discount_amount <= subtotal + shipping_fee
            )
        ");

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_payment_status_check
            CHECK (payment_status IN ('unpaid', 'paid', 'failed', 'refunded'))
        ");

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_order_status_check
            CHECK (order_status IN ('pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
