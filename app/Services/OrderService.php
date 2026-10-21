<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Enums\EnrollmentSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Notifications\OrderReceiptNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly EnrollmentService $enrollments,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(User $user, Course $course): Order
    {
        if (! $course->isPublished()) {
            throw ValidationException::withMessages([
                'course' => 'This course is not available for purchase.',
            ]);
        }

        if ($course->isFree()) {
            throw ValidationException::withMessages([
                'course' => 'This course is free. Enroll without checkout.',
            ]);
        }

        if ($this->enrollments->hasAccess($user, $course)) {
            throw ValidationException::withMessages([
                'course' => 'You already have access to this course.',
            ]);
        }

        return DB::transaction(function () use ($course, $user): Order {
            User::query()->lockForUpdate()->findOrFail($user->id);

            $existing = Order::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->pending()
                ->where(fn (Builder $query): Builder => $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now()))
                ->latest()
                ->first();

            if ($existing?->payment_url) {
                return $existing;
            }

            $order = $existing ?? Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'course_id' => $course->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'course_title' => $course->title,
                'currency' => $course->currency ?: config('brightside.currency', 'IDR'),
                'subtotal_minor' => $course->price_minor,
                'discount_minor' => 0,
                'tax_minor' => 0,
                'total_minor' => $course->price_minor,
                'status' => OrderStatus::Pending,
                'payment_provider' => $this->gateway->name(),
                'expires_at' => now()->addHours((int) config('brightside.order_expiry_hours', 24)),
            ]);

            try {
                $initiation = $this->gateway->initiate($order, $user);

                $order->forceFill([
                    'payment_url' => $initiation->url,
                    'payment_reference' => $initiation->reference,
                ])->save();
            } catch (Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'payment' => 'We could not start the payment. Please try again shortly.',
                ]);
            }

            $this->auditLogger->log(
                action: 'order.created',
                subject: $order,
                newValues: ['course_id' => $course->id, 'total_minor' => $order->total_minor],
                actor: $user,
            );

            return $order->refresh();
        });
    }

    /**
     * Mark an order paid and provision enrollment idempotently.
     *
     * @param  array<string, mixed>  $payload
     */
    public function complete(
        Order $order,
        PaymentStatus $status = PaymentStatus::Settlement,
        array $payload = [],
        ?string $method = null,
        ?string $providerReference = null,
    ): Order {
        return DB::transaction(function () use ($order, $status, $payload, $method, $providerReference): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($locked->isPaid()) {
                return $locked;
            }

            $locked->forceFill([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
                'payment_provider' => $locked->payment_provider ?: $this->gateway->name(),
            ])->save();

            Payment::create([
                'order_id' => $locked->id,
                'provider' => $locked->payment_provider,
                'provider_reference' => $providerReference ?: ($locked->payment_reference ?: 'PAY-'.Str::upper(Str::random(20))),
                'provider_status' => $status->value,
                'method' => $method,
                'amount_minor' => $locked->total_minor,
                'raw_payload' => $payload ?: null,
                'paid_at' => now(),
            ]);

            $this->enrollments->enroll(
                $locked->user,
                $locked->course,
                EnrollmentSource::Purchase,
                $locked,
            );

            $this->auditLogger->log(
                action: 'order.paid',
                subject: $locked,
                newValues: [
                    'status' => OrderStatus::Paid->value,
                    'payment_status' => $status->value,
                    'total_minor' => $locked->total_minor,
                ],
            );

            OrderReceiptNotification::sendFor($locked);

            return $locked->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function markUnpaid(
        Order $order,
        OrderStatus $status,
        PaymentStatus $paymentStatus,
        array $payload = [],
    ): Order {
        if ($order->isPaid()) {
            return $order;
        }

        $order->forceFill(['status' => $status])->save();

        Payment::create([
            'order_id' => $order->id,
            'provider' => $order->payment_provider ?: $this->gateway->name(),
            'provider_reference' => 'PAY-'.Str::upper(Str::random(20)),
            'provider_status' => $paymentStatus->value,
            'amount_minor' => $order->total_minor,
            'raw_payload' => $payload ?: null,
        ]);

        return $order->refresh();
    }

    public function refund(Order $order, ?string $reason = null): Order
    {
        if (! $order->isPaid()) {
            throw ValidationException::withMessages([
                'order' => 'Only paid orders can be refunded.',
            ]);
        }

        DB::transaction(function () use ($order, $reason): void {
            $order->forceFill([
                'status' => OrderStatus::Refunded,
                'notes' => trim(($order->notes ? $order->notes."\n" : '').'Refunded: '.($reason ?: 'manual adjustment')),
            ])->save();

            $enrollment = $order->enrollment;

            if ($enrollment) {
                $this->enrollments->revoke($enrollment, $reason ?: 'Order refunded');
            }

            $this->auditLogger->log(
                action: 'order.refunded',
                subject: $order,
                oldValues: ['status' => OrderStatus::Paid->value],
                newValues: ['status' => OrderStatus::Refunded->value],
                reason: $reason,
            );
        });

        return $order->refresh();
    }

    /**
     * Record a gateway notification once, returning false on duplicates.
     *
     * @param  array<string, mixed>  $payload
     */
    public function recordWebhook(string $provider, string $externalId, string $eventType, array $payload): bool
    {
        $event = WebhookEvent::firstOrCreate(
            ['provider' => $provider, 'external_id' => $externalId],
            ['event_type' => $eventType, 'payload' => $payload],
        );

        if (! $event->wasRecentlyCreated) {
            return false;
        }

        $event->forceFill(['processed_at' => now()])->save();

        return true;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'BS-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
