<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            $table->string('product_name', 200);
            $table->string('variant_name', 200);
            $table->string('sku', 100)->nullable();

            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 12, 2);

            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE order_items
            ADD CONSTRAINT order_items_value_check
            CHECK (
                unit_price >= 0
                AND quantity > 0
                AND total_price >= 0
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
