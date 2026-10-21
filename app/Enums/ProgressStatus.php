<?php

namespace App\Enums;

enum ProgressStatus: string
{
    case InProgress = 'InProgress';
    case Completed = 'Completed';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::InProgress => 'warning',
            self::Completed => 'success',
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
