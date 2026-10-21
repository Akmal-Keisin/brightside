<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Config;

function webhookPayload(Order $order, string $status = 'settlement', string $transactionId = 'txn-123'): array
{
    $gross = number_format($order->total_minor, 2, '.', '');
    $serverKey = Config::get('brightside.midtrans.server_key');

    return [
        'order_id' => $order->order_number,
        'status_code' => '200',
        'gross_amount' => $gross,
        'signature_key' => hash('sha512', $order->order_number.'200'.$gross.$serverKey),
        'transaction_status' => $status,
        'transaction_id' => $transactionId,
        'payment_type' => 'qris',
        'fraud_status' => 'accept',
    ];
}

beforeEach(function () {
    Config::set('brightside.midtrans.server_key', 'test-server-key');
});

test('a valid settlement webhook marks the order paid and enrolls the member', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending, 'total_minor' => 249000]);
    $payload = webhookPayload($order);

    $this->postJson(route('webhooks.midtrans'), $payload)
        ->assertOk()
        ->assertJson(['message' => 'OK']);

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $order->user_id,
        'course_id' => $order->course_id,
        'order_id' => $order->id,
    ]);

    $this->assertDatabaseHas('webhook_events', [
        'provider' => 'midtrans',
        'external_id' => 'txn-123',
    ]);
});

test('duplicate webhook notifications are processed once', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);
    $payload = webhookPayload($order);

    $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();
    $this->postJson(route('webhooks.midtrans'), $payload)
        ->assertOk()
        ->assertJson(['message' => 'Already processed.']);

    expect($order->fresh()->payments()->count())->toBe(1);
});

test('a webhook with an invalid signature is rejected', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);
    $payload = webhookPayload($order);
    $payload['signature_key'] = str_repeat('0', 128);

    $this->postJson(route('webhooks.midtrans'), $payload)->assertForbidden();

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('a webhook with a mismatched amount is rejected', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending, 'total_minor' => 249000]);
    $payload = webhookPayload($order);
    $payload['gross_amount'] = '1000.00';
    $payload['signature_key'] = hash('sha512', $order->order_number.'2001000.00'.Config::get('brightside.midtrans.server_key'));

    $this->postJson(route('webhooks.midtrans'), $payload)->assertStatus(422);

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});
