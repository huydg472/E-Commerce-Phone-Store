<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('spec_name', 150);
            $table->text('spec_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'spec_name']);
        });

        DB::statement("
            ALTER TABLE product_specifications
            ADD CONSTRAINT product_specifications_sort_order_check
            CHECK (sort_order >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};
