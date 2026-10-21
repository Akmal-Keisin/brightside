<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 120);
            $table->string('customer_email', 190);
            $table->string('course_title', 180);
            $table->char('currency', 3);
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('tax_minor')->default(0);
            $table->unsignedBigInteger('total_minor');
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_provider', 30)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_url', 500)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'expires_at']);
            $table->index('course_id');
            $table->index('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
