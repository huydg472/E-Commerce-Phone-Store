<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('receiver_name', 150);
            $table->string('receiver_phone', 20);
            $table->string('province', 100);
            $table->string('district', 100);
            $table->string('ward', 100);
            $table->string('address_detail', 255);
            $table->text('note')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        DB::statement("
            CREATE UNIQUE INDEX shipping_addresses_one_default_per_user
            ON shipping_addresses (user_id)
            WHERE is_default = true
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
