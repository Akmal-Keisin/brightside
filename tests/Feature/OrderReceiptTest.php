<?php

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderReceiptNotification;
use App\Services\OrderService;
use Illuminate\Support\Facades\Notification;

test('a completed order queues exactly one receipt', function () {
    Notification::fake();

    $order = Order::factory()->create();
    $service = app(OrderService::class);

    $service->complete($order, method: 'qris', providerReference: 'receipt-test-ref');
    $service->complete($order->fresh());

    Notification::assertSentOnDemandTimes(OrderReceiptNotification::class, 1);
    Notification::assertSentOnDemand(
        OrderReceiptNotification::class,
        fn (OrderReceiptNotification $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === $order->customer_email
            && in_array('mail', $channels, true)
            && $notification->order->is($order),
    );
});

test('an admin can resend a receipt for a paid order', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $order = Order::factory()->paid()->create();

    $this->actingAs($admin)
        ->post(route('admin.orders.receipt', $order->order_number))
        ->assertRedirect()
        ->assertSessionHas('success');

    Notification::assertSentOnDemandTimes(OrderReceiptNotification::class, 1);
});

test('an admin cannot resend a receipt for an unpaid order', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.orders.receipt', $order->order_number))
        ->assertRedirect()
        ->assertSessionHas('error');

    Notification::assertNothingSent();
});
