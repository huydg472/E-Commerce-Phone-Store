<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();
        });

        DB::statement('
            UPDATE favorites
            SET product_variant_id = first_variant.id
            FROM (
                SELECT DISTINCT ON (product_id) id, product_id
                FROM product_variants
                ORDER BY product_id, id
            ) AS first_variant
            WHERE favorites.product_id = first_variant.product_id
              AND favorites.product_variant_id IS NULL
        ');

        DB::table('favorites')
            ->whereNull('product_variant_id')
            ->delete();

        DB::statement('ALTER TABLE favorites ALTER COLUMN product_variant_id SET NOT NULL');

        Schema::table('favorites', function (Blueprint $table) {
            $table->unique(['user_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_variant_id']);
            $table->dropConstrainedForeignId('product_variant_id');
            $table->unique(['user_id', 'product_id']);
        });
    }
};
