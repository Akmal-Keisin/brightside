<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'Admin';
    case Member = 'Member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Member => 'Member',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'warning',
            self::Member => 'info',
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
