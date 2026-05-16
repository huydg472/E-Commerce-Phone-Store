<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('color', 100);
            $table->string('storage', 50);
            $table->string('ram', 50);
            $table->string('sku', 100)->unique();
            $table->decimal('import_price', 12, 2)->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->string('status', 20)->default('active');
            $table->text('description')->nullable();          
            $table->timestamps();

            $table->unique(['product_id', 'color', 'storage', 'ram']);
        });

        DB::statement("
            ALTER TABLE product_variants
            ADD CONSTRAINT product_variants_price_check
            CHECK (
                price >= 0
                AND quantity >= 0
                AND (
                    sale_price IS NULL
                    OR (
                        sale_price >= 0
                        AND sale_price <= price
                    )
                )
                AND (
                    import_price IS NULL
                    OR import_price >= 0
                )
            )
        ");

        DB::statement("
            ALTER TABLE product_variants
            ADD CONSTRAINT product_variants_status_check
            CHECK (status IN ('active', 'inactive', 'out_of_stock'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
