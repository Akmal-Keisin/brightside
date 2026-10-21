<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();

            $table->index(['course_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
