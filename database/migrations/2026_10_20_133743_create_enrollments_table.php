<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('source', 20);
            $table->timestamp('enrolled_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->unsignedSmallInteger('completed_contents_count')->default(0);
            $table->unsignedSmallInteger('total_contents_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index(['course_id', 'completed_at']);
            $table->index(['user_id', 'last_accessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
