<?php

namespace App\Http\Controllers\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, OrderService $orders): JsonResponse
    {
        $payload = $request->all();

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'max:32'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'numeric'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string', 'max:30'],
            'transaction_id' => ['nullable', 'string', 'max:120'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'fraud_status' => ['nullable', 'string', 'max:30'],
        ]);

        $serverKey = (string) config('brightside.midtrans.server_key');

        if ($serverKey === '') {
            Log::warning('Midtrans webhook received without a configured server key.');

            return response()->json(['message' => 'Gateway not configured.'], 503);
        }

        $expected = hash('sha512',
            $validated['order_id']
            .$validated['status_code']
            .$validated['gross_amount']
            .$serverKey,
        );

        if (! hash_equals($expected, $validated['signature_key'])) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = Order::query()->where('order_number', $validated['order_id'])->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ((int) round((float) $validated['gross_amount']) !== $order->total_minor) {
            Log::warning('Midtrans amount mismatch.', ['order' => $order->order_number]);

            return response()->json(['message' => 'Amount mismatch.'], 422);
        }

        $recorded = $orders->recordWebhook(
            'midtrans',
            $validated['transaction_id'] ?? $validated['order_id'].'-'.$validated['status_code'],
            $validated['transaction_status'],
            $payload,
        );

        if (! $recorded) {
            return response()->json(['message' => 'Already processed.']);
        }

        $this->apply($order, $orders, $validated);

        return response()->json(['message' => 'OK']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function apply(Order $order, OrderService $orders, array $data): void
    {
        $status = $data['transaction_status'];
        $fraud = $data['fraud_status'] ?? null;

        if ($status === 'capture' && $fraud !== 'accept') {
            return;
        }

        if (in_array($status, ['settlement', 'capture'], true)) {
            $orders->complete(
                $order,
                PaymentStatus::from($status === 'capture' ? 'Capture' : 'Settlement'),
                $data,
                $data['payment_type'] ?? null,
                $data['transaction_id'] ?? null,
            );

            return;
        }

        $mapping = [
            'deny' => [OrderStatus::Failed, PaymentStatus::Deny],
            'cancel' => [OrderStatus::Cancelled, PaymentStatus::Cancel],
            'expire' => [OrderStatus::Expired, PaymentStatus::Expire],
            'failure' => [OrderStatus::Failed, PaymentStatus::Failure],
        ];

        if (isset($mapping[$status])) {
            [$orderStatus, $paymentStatus] = $mapping[$status];

            $orders->markUnpaid($order, $orderStatus, $paymentStatus, $data);
        }

        if ($status === 'refund' || $status === 'partial_refund') {
            $orders->refund($order, 'Midtrans refund notification');
        }
    }
}
