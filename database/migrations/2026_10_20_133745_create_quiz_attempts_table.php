<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_number');
            $table->string('status', 20)->default('in_progress');
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('passed')->nullable();
            $table->json('question_order')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'user_id', 'attempt_number']);
            $table->index(['user_id', 'quiz_id']);
            $table->index(['quiz_id', 'passed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
