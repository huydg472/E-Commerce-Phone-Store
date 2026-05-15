<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type', 30);
            $table->integer('quantity_before');
            $table->integer('quantity_change');
            $table->integer('quantity_after');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_variant_id', 'created_at']);
            $table->index('type');
        });

        DB::statement("
            ALTER TABLE stock_logs
            ADD CONSTRAINT stock_logs_quantity_check
            CHECK (quantity_before >= 0 AND quantity_after >= 0 AND quantity_after = quantity_before + quantity_change)
        ");

        DB::statement("
            ALTER TABLE stock_logs
            ADD CONSTRAINT stock_logs_type_check
            CHECK (type IN ('import', 'sale', 'cancel_order', 'return', 'adjustment'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};