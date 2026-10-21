<?php

use App\Enums\OrderStatus;
use App\Models\Order;

test('pending orders past the payment window are expired', function () {
    $expired = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'expires_at' => now()->subMinute(),
    ]);

    $future = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'expires_at' => now()->addHour(),
    ]);

    $paid = Order::factory()->paid()->create([
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('orders:expire')->assertSuccessful();

    expect($expired->fresh()->status)->toBe(OrderStatus::Expired)
        ->and($future->fresh()->status)->toBe(OrderStatus::Pending)
        ->and($paid->fresh()->status)->toBe(OrderStatus::Paid);
});
