<?php

namespace App\Support;

class Money
{
    /**
     * Format an integer minor amount for display.
     */
    public static function format(int $amount, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?: (string) config('brightside.currency', 'IDR'));

        if ($currency === 'IDR') {
            return 'Rp '.number_format($amount, 0, ',', '.');
        }

        $symbol = match ($currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currency.' ',
        };

        return $symbol.number_format($amount / 100, 2, '.', ',');
    }

    public static function isFree(int $amount): bool
    {
        return $amount === 0;
    }
}
