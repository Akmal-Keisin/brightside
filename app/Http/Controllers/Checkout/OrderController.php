<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user()
            ->orders()
            ->with('course')
            ->latest()
            ->paginate(10);

        return Inertia::render('orders/Index', [
            'orders' => $orders->through(fn (Order $order): array => $this->summary($order)),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['course', 'payments', 'enrollment']);

        return Inertia::render('orders/Show', [
            'order' => [
                ...$this->summary($order),
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'subtotal' => Money::format($order->subtotal_minor, $order->currency),
                'total' => Money::format($order->total_minor, $order->currency),
                'paid_at' => $order->paid_at?->format('M j, Y · H:i'),
                'expires_at' => $order->expires_at?->toIso8601String(),
                'payment_url' => $order->isPayable() ? $order->payment_url : null,
                'payments' => $order->payments->map(fn ($payment): array => [
                    'status' => $payment->provider_status,
                    'method' => $payment->method,
                    'amount' => Money::format($payment->amount_minor, $order->currency),
                    'paid_at' => $payment->paid_at?->format('M j, Y · H:i'),
                ])->all(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'status' => $order->status?->value,
            'status_label' => $order->status?->label(),
            'course_title' => $order->course_title,
            'course_slug' => $order->course?->slug,
            'amount' => Money::format($order->total_minor, $order->currency),
            'created_at' => $order->created_at?->format('M j, Y'),
            'is_payable' => $order->isPayable(),
        ];
    }
}
