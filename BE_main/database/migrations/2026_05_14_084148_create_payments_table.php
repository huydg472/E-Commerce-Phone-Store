<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('payment_method', 50);
            $table->string('payment_status', 30)->default('pending');
            $table->decimal('amount', 12, 2);
            $table->string('transaction_code', 100)->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique('order_id');
        });

        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_amount_check
            CHECK (amount >= 0)
        ");

        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_method_check
            CHECK (payment_method IN ('cod', 'bank_transfer', 'vnpay', 'momo'))
        ");

        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_status_check
            CHECK (payment_status IN ('pending', 'paid', 'failed', 'cancelled', 'refunded'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
