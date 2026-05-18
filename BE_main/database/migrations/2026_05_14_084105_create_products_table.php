<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')
                ->constrained('brands')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->string('name', 150)->unique();
            $table->string('slug', 180)->unique();
            $table->string('thumbnail_url', 500)->nullable();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT products_status_check
            CHECK (status IN ('active', 'inactive'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
