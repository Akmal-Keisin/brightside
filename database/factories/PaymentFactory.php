<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'fake',
            'provider_reference' => 'PAY-'.Str::upper(Str::random(10)),
            'provider_status' => 'settlement',
            'method' => 'qris',
            'amount_minor' => 149000,
            'raw_payload' => [],
            'paid_at' => now(),
        ];
    }
}
