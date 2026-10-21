<?php

use App\Contracts\PaymentGateway;
use App\Data\PaymentInitiation;
use App\Data\PaymentSnapshot;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderReceiptNotification;
use App\Services\ReconcilePendingPayments;
use Illuminate\Support\Facades\Notification;

test('reconciliation applies a settled gateway status to a pending order', function () {
    Notification::fake();

    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'payment_reference' => 'snap-token',
        'created_at' => now()->subMinutes(20),
    ]);

    $gateway = new class implements PaymentGateway
    {
        public function name(): string
        {
            return 'fake';
        }

        public function initiate(Order $order, User $user): PaymentInitiation
        {
            throw new LogicException('Not used during reconciliation.');
        }

        public function fetch(Order $order): ?PaymentSnapshot
        {
            return new PaymentSnapshot(
                status: PaymentStatus::Settlement,
                fraudStatus: 'accept',
                reference: 'midtrans-transaction-1',
                method: 'qris',
                payload: ['transaction_status' => 'settlement'],
            );
        }
    };

    app()->instance(PaymentGateway::class, $gateway);

    $applied = app(ReconcilePendingPayments::class)->handle();

    expect($applied)->toBe(1)
        ->and($order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($order->fresh()->payments()->count())->toBe(1)
        ->and($order->fresh()->enrollment)->not->toBeNull();

    Notification::assertSentOnDemand(OrderReceiptNotification::class);
});

test('reconciliation ignores recent pending orders', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'payment_reference' => 'snap-token',
        'created_at' => now()->subMinutes(2),
    ]);

    $gateway = new class implements PaymentGateway
    {
        public function name(): string
        {
            return 'fake';
        }

        public function initiate(Order $order, User $user): PaymentInitiation
        {
            throw new LogicException('Not used during reconciliation.');
        }

        public function fetch(Order $order): ?PaymentSnapshot
        {
            throw new LogicException('Recent orders should not be fetched.');
        }
    };

    app()->instance(PaymentGateway::class, $gateway);

    expect(app(ReconcilePendingPayments::class)->handle())->toBe(0)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});
