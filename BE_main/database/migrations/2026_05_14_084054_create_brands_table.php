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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('slug', 180)->unique();
            $table->string('logo_url', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE brands
            ADD CONSTRAINT brands_status_check
            CHECK (status IN ('active', 'inactive'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
