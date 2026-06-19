<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('site_settings')) {
            return;
        }

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 150)->default('ZinMobile');
            $table->string('brand_name', 150)->default('ZinMobile');
            $table->string('slogan', 255)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('favicon_url', 500)->nullable();
            $table->string('support_phone', 30)->nullable();
            $table->string('support_email', 150)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('footer_description')->nullable();
            $table->string('facebook_url', 500)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->string('tiktok_url', 500)->nullable();
            $table->string('youtube_url', 500)->nullable();
            $table->string('zalo_url', 500)->nullable();
            $table->unsignedInteger('shipping_fee_standard')->default(0);
            $table->unsignedInteger('shipping_fee_express')->default(40000);
            $table->text('cash_on_delivery_note')->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 150)->nullable();
            $table->text('bank_transfer_note')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
