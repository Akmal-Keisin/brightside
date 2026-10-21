<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderReceiptNotification;
use App\Services\OrderService;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
        ]);

        $orders = Order::query()
            ->with(['user', 'course'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $search): Builder => $query
                ->where(fn (Builder $query): Builder => $query
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders->through(fn (Order $order): array => [
                'order_number' => $order->order_number,
                'customer' => $order->customer_name,
                'email' => $order->customer_email,
                'course' => $order->course_title,
                'amount' => Money::format($order->total_minor, $order->currency),
                'status' => $order->status?->value,
                'status_label' => $order->status?->label(),
                'created_at' => $order->created_at?->format('M j, Y · H:i'),
            ]),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'statuses' => OrderStatus::options(),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['user', 'course', 'payments', 'enrollment']);

        return Inertia::render('admin/orders/Show', [
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status?->value,
                'status_label' => $order->status?->label(),
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'course_title' => $order->course_title,
                'course_slug' => $order->course?->slug,
                'subtotal' => Money::format($order->subtotal_minor, $order->currency),
                'total' => Money::format($order->total_minor, $order->currency),
                'payment_provider' => $order->payment_provider,
                'created_at' => $order->created_at?->format('M j, Y · H:i'),
                'paid_at' => $order->paid_at?->format('M j, Y · H:i'),
                'notes' => $order->notes,
                'is_paid' => $order->isPaid(),
                'is_refunded' => $order->isRefunded(),
                'user' => $order->user ? [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'id' => $order->user->id,
                ] : null,
                'enrollment' => $order->enrollment ? [
                    'id' => $order->enrollment->id,
                    'progress_percent' => $order->enrollment->progress_percent,
                    'revoked_at' => $order->enrollment->revoked_at?->toIso8601String(),
                ] : null,
                'payments' => $order->payments->map(fn ($payment): array => [
                    'provider' => $payment->provider,
                    'reference' => $payment->provider_reference,
                    'status' => $payment->provider_status,
                    'method' => $payment->method,
                    'amount' => Money::format($payment->amount_minor, $order->currency),
                    'paid_at' => $payment->paid_at?->format('M j, Y · H:i'),
                ])->all(),
            ],
        ]);
    }

    public function refund(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->orders->refund($order, $validated['reason']);

        return back()->with('success', 'Order refunded and enrollment revoked.');
    }

    public function receipt(Order $order): RedirectResponse
    {
        if (! $order->isPaid()) {
            return back()->with('error', 'Only paid orders have a receipt to resend.');
        }

        OrderReceiptNotification::sendFor($order);

        return back()->with('success', 'Receipt queued for '.$order->customer_email.'.');
    }
}
