<?php

namespace App\Data;

final readonly class PaymentInitiation
{
    public function __construct(
        public string $url,
        public ?string $reference = null,
        public ?string $token = null,
    ) {}
}
