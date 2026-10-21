<?php

namespace App\Contracts;

use App\Data\PaymentInitiation;
use App\Data\PaymentSnapshot;
use App\Models\Order;
use App\Models\User;

interface PaymentGateway
{
    public function name(): string;

    public function initiate(Order $order, User $user): PaymentInitiation;

    public function fetch(Order $order): ?PaymentSnapshot;
}
