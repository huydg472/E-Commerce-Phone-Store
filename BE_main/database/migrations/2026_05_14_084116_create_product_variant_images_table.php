<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->string('image_url', 500);
            $table->string('alt_text', 225)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_variant_id', 'image_url']);
        });

        DB::statement("
            ALTER TABLE product_variant_images
            ADD CONSTRAINT product_variant_images_sort_order_check
            CHECK (sort_order >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_images');
    }
};
