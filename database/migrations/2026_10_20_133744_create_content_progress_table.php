<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('in_progress');
            $table->unsignedInteger('progress_seconds')->default(0);
            $table->unsignedTinyInteger('best_score')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'content_id']);
            $table->index(['user_id', 'course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_progress');
    }
};
