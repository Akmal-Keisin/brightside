<?php

namespace App\Enums;

enum EnrollmentSource: string
{
    case Purchase = 'Purchase';
    case Free = 'Free';
    case Grant = 'Grant';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Free => 'Free',
            self::Grant => 'Admin grant',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Purchase => 'success',
            self::Free => 'info',
            self::Grant => 'warning',
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
