<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_category_id')
                ->nullable()
                ->constrained('news_categories')
                ->nullOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->string('excerpt', 500);
            $table->longText('content');
            $table->string('featured_image_url', 2048)->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('reading_minutes')->default(3);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_featured', 'published_at']);
            $table->index(['news_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
