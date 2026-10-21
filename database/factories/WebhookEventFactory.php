<?php

namespace Database\Factories;

use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEvent>
 */
class WebhookEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'midtrans',
            'external_id' => (string) Str::uuid(),
            'event_type' => 'settlement',
            'payload' => [],
            'processed_at' => null,
        ];
    }
}
