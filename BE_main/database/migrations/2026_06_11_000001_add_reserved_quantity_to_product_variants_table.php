<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('reserved_quantity')->default(0)->after('quantity');
        });

        DB::statement("
            ALTER TABLE product_variants
            ADD CONSTRAINT product_variants_reserved_quantity_check
            CHECK (
                quantity >= 0
                AND reserved_quantity >= 0
                AND quantity >= reserved_quantity
            )
        ");
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('reserved_quantity');
        });
    }
};
