<?php

namespace App\Http\Controllers\Checkout;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DevelopmentPaymentController extends Controller
{
    public function __invoke(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless(app()->environment('local', 'testing'), 404);
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->isPayable(), 422);

        $orders->complete(
            $order,
            PaymentStatus::Settlement,
            ['source' => 'local-simulation'],
            'local',
        );

        return to_route('checkout.status', $order)
            ->with('success', 'Test payment marked as paid.');
    }
}
