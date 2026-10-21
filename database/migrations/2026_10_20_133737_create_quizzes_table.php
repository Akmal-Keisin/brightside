<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('passing_score')->default(70);
            $table->unsignedSmallInteger('max_attempts')->nullable();
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_answers_after_submit')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
