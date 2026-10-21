<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30);
            $table->string('external_id', 120);
            $table->string('event_type', 80);
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
