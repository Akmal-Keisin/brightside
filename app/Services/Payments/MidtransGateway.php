<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Data\PaymentInitiation;
use App\Data\PaymentSnapshot;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransGateway implements PaymentGateway
{
    public function initiate(Order $order, User $user): PaymentInitiation
    {
        $serverKey = (string) config('brightside.midtrans.server_key');

        if ($serverKey === '') {
            throw new RuntimeException('Midtrans server key is not configured.');
        }

        $baseUrl = rtrim((string) config('brightside.midtrans.snap_base_url'), '/');

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post($baseUrl.'/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => $order->total_minor,
                ],
                'item_details' => [[
                    'id' => (string) $order->course_id,
                    'price' => $order->total_minor,
                    'quantity' => 1,
                    'name' => mb_substr($order->course_title, 0, 50),
                ]],
                'customer_details' => [
                    'first_name' => mb_substr($user->name, 0, 50),
                    'email' => $user->email,
                ],
                'callbacks' => [
                    'finish' => route('checkout.status', $order),
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Midtrans rejected the transaction: '.$response->body());
        }

        /** @var array{redirect_url?: string, token?: string} $data */
        $data = $response->json();

        if (empty($data['redirect_url'])) {
            throw new RuntimeException('Midtrans did not return a payment URL.');
        }

        return new PaymentInitiation(
            url: $data['redirect_url'],
            reference: $data['token'] ?? null,
            token: $data['token'] ?? null,
        );
    }

    public function name(): string
    {
        return 'midtrans';
    }

    public function fetch(Order $order): ?PaymentSnapshot
    {
        $serverKey = (string) config('brightside.midtrans.server_key');

        if ($serverKey === '') {
            throw new RuntimeException('Midtrans server key is not configured.');
        }

        $baseUrl = rtrim((string) config('brightside.midtrans.api_base_url'), '/');

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(15)
            ->get($baseUrl.'/v2/'.$order->order_number.'/status');

        if ($response->status() === 404) {
            return null;
        }

        if ($response->failed()) {
            throw new RuntimeException('Midtrans status lookup failed: '.$response->body());
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        $status = match ($data['transaction_status'] ?? null) {
            'settlement' => PaymentStatus::Settlement,
            'capture' => PaymentStatus::Capture,
            'pending' => PaymentStatus::Pending,
            'deny' => PaymentStatus::Deny,
            'cancel' => PaymentStatus::Cancel,
            'expire' => PaymentStatus::Expire,
            'failure' => PaymentStatus::Failure,
            'refund' => PaymentStatus::Refund,
            'partial_refund' => PaymentStatus::PartialRefund,
            default => null,
        };

        if (! $status instanceof PaymentStatus) {
            return null;
        }

        return new PaymentSnapshot(
            status: $status,
            fraudStatus: $data['fraud_status'] ?? null,
            reference: $data['transaction_id'] ?? null,
            method: $data['payment_type'] ?? null,
            payload: $data,
        );
    }
}
