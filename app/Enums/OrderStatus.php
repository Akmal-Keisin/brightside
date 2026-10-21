<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'Pending';
    case Paid = 'Paid';
    case Failed = 'Failed';
    case Expired = 'Expired';
    case Cancelled = 'Cancelled';
    case Refunded = 'Refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting payment',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Expired => 'muted',
            self::Cancelled => 'muted',
            self::Refunded => 'warning',
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
