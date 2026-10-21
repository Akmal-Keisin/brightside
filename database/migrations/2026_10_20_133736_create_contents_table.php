<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index();
            $table->string('title', 180);
            $table->unsignedSmallInteger('position')->default(1);
            $table->boolean('is_free_preview')->default(false);
            $table->unsignedSmallInteger('estimated_minutes')->default(5);
            $table->longText('body')->nullable();
            $table->string('video_provider', 20)->nullable();
            $table->string('video_reference', 255)->nullable();
            $table->unsignedInteger('video_duration_seconds')->nullable();
            $table->string('captions_path')->nullable();
            $table->longText('transcript')->nullable();
            $table->timestamps();

            $table->index(['chapter_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
