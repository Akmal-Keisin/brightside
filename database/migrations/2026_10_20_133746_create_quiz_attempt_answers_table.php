<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question_text');
            $table->string('selected_option_text', 500)->nullable();
            $table->string('correct_option_text', 500);
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['quiz_attempt_id', 'quiz_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
    }
};
