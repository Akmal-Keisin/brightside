<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        $email = fake()->safeEmail();

        return [
            'order_number' => 'BS-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'customer_name' => $name,
            'customer_email' => $email,
            'course_title' => fake()->sentence(4),
            'currency' => 'IDR',
            'subtotal_minor' => 149000,
            'discount_minor' => 0,
            'tax_minor' => 0,
            'total_minor' => 149000,
            'status' => OrderStatus::Pending,
            'payment_provider' => 'fake',
            'payment_reference' => 'FAKE-'.Str::upper(Str::random(10)),
            'payment_url' => null,
            'paid_at' => null,
            'expires_at' => now()->addDay(),
            'notes' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Refunded,
            'paid_at' => now()->subDay(),
        ]);
    }
}
