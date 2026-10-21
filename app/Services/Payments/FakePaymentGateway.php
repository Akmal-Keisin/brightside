<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Data\PaymentInitiation;
use App\Data\PaymentSnapshot;
use App\Models\Order;
use App\Models\User;

class FakePaymentGateway implements PaymentGateway
{
    public function initiate(Order $order, User $user): PaymentInitiation
    {
        return new PaymentInitiation(
            url: route('checkout.status', $order),
            reference: 'FAKE-'.$order->order_number,
            token: 'fake-token',
        );
    }

    public function name(): string
    {
        return 'fake';
    }

    public function fetch(Order $order): ?PaymentSnapshot
    {
        return null;
    }
}
