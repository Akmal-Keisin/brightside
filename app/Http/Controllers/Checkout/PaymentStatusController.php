<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentStatusController extends Controller
{
    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['course', 'enrollment']);

        return Inertia::render('checkout/Status', [
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status?->value,
                'status_label' => $order->status?->label(),
                'course_title' => $order->course_title,
                'course_slug' => $order->course?->slug,
                'amount' => Money::format($order->total_minor, $order->currency),
                'payment_url' => $order->isPayable() ? $order->payment_url : null,
                'is_payable' => $order->isPayable(),
                'is_paid' => $order->isPaid(),
                'expires_at' => $order->expires_at?->toIso8601String(),
            ],
            'canSimulate' => app()->environment('local', 'testing')
                && $order->isPayable()
                && $order->payment_provider === 'fake',
        ]);
    }
}
