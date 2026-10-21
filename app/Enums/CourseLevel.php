<?php

namespace App\Enums;

enum CourseLevel: string
{
    case Beginner = 'Beginner';
    case Intermediate = 'Intermediate';
    case Advanced = 'Advanced';

    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Beginner',
            self::Intermediate => 'Intermediate',
            self::Advanced => 'Advanced',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Beginner => 'info',
            self::Intermediate => 'warning',
            self::Advanced => 'danger',
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
