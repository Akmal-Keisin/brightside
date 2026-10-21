<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Data\PaymentSnapshot;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Throwable;

class ReconcilePendingPayments
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly OrderService $orders,
    ) {}

    public function handle(int $staleMinutes = 10, int $limit = 50): int
    {
        $applied = 0;

        Order::query()
            ->pending()
            ->whereNotNull('payment_reference')
            ->where('created_at', '<=', now()->subMinutes(max(1, $staleMinutes)))
            ->oldest()
            ->limit($limit)
            ->get()
            ->each(function (Order $order) use (&$applied): void {
                try {
                    $snapshot = $this->gateway->fetch($order);
                } catch (Throwable $exception) {
                    report($exception);

                    return;
                }

                if (! $snapshot instanceof PaymentSnapshot) {
                    return;
                }

                if ($this->apply($order, $snapshot)) {
                    $applied++;
                }
            });

        return $applied;
    }

    private function apply(Order $order, PaymentSnapshot $snapshot): bool
    {
        $payload = $snapshot->payload;

        return match ($snapshot->status) {
            PaymentStatus::Settlement => $this->pay($order, $snapshot, PaymentStatus::Settlement),
            PaymentStatus::Capture => $snapshot->fraudStatus === 'challenge'
                ? false
                : $this->pay($order, $snapshot, PaymentStatus::Capture),
            PaymentStatus::Deny => $this->close($order, OrderStatus::Failed, PaymentStatus::Deny, $payload),
            PaymentStatus::Cancel => $this->close($order, OrderStatus::Cancelled, PaymentStatus::Cancel, $payload),
            PaymentStatus::Expire => $this->close($order, OrderStatus::Expired, PaymentStatus::Expire, $payload),
            PaymentStatus::Failure => $this->close($order, OrderStatus::Failed, PaymentStatus::Failure, $payload),
            default => false,
        };
    }

    private function pay(Order $order, PaymentSnapshot $snapshot, PaymentStatus $status): bool
    {
        $this->orders->complete(
            order: $order,
            status: $status,
            payload: $snapshot->payload,
            method: $snapshot->method,
            providerReference: $snapshot->reference,
        );

        return true;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function close(Order $order, OrderStatus $status, PaymentStatus $paymentStatus, array $payload): bool
    {
        $this->orders->markUnpaid($order, $status, $paymentStatus, $payload);

        return true;
    }
}
