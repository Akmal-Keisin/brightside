<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->string('subtitle', 240)->nullable();
            $table->longText('description');
            $table->string('thumbnail_path')->nullable();
            $table->string('thumbnail_alt', 180)->nullable();
            $table->string('trailer_url', 500)->nullable();
            $table->unsignedBigInteger('price_minor')->default(0);
            $table->char('currency', 3)->default('IDR');
            $table->string('level', 20)->default('beginner');
            $table->string('language', 5)->default('en');
            $table->json('learning_outcomes')->nullable();
            $table->json('requirements')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedSmallInteger('contents_count')->default(0);
            $table->unsignedInteger('enrollments_count')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->decimal('rating_avg', 3, 2)->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);
            $table->index('price_minor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
