<?php

namespace App\Data;

use App\Enums\PaymentStatus;

final readonly class PaymentSnapshot
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public PaymentStatus $status,
        public ?string $fraudStatus = null,
        public ?string $reference = null,
        public ?string $method = null,
        public array $payload = [],
    ) {}
}
