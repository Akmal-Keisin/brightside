<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->unsignedTinyInteger('points')->default(1);
            $table->timestamps();

            $table->index(['quiz_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
