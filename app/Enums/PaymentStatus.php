<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'Pending';
    case Settlement = 'Settlement';
    case Capture = 'Capture';
    case Deny = 'Deny';
    case Cancel = 'Cancel';
    case Expire = 'Expire';
    case Refund = 'Refund';
    case PartialRefund = 'PartialRefund';
    case Failure = 'Failure';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Settlement => 'Settlement',
            self::Capture => 'Capture',
            self::Deny => 'Denied',
            self::Cancel => 'Cancelled',
            self::Expire => 'Expired',
            self::Refund => 'Refunded',
            self::PartialRefund => 'Partially refunded',
            self::Failure => 'Failure',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Settlement => 'success',
            self::Capture => 'success',
            self::Deny => 'danger',
            self::Cancel => 'muted',
            self::Expire => 'muted',
            self::Refund => 'warning',
            self::PartialRefund => 'warning',
            self::Failure => 'danger',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
