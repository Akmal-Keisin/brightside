<?php

namespace App\Enums;

enum QuizAttemptStatus: string
{
    case InProgress = 'InProgress';
    case Submitted = 'Submitted';
    case Passed = 'Passed';
    case Failed = 'Failed';
    case Expired = 'Expired';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'In progress',
            self::Submitted => 'Submitted',
            self::Passed => 'Passed',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::InProgress => 'warning',
            self::Submitted => 'info',
            self::Passed => 'success',
            self::Failed => 'danger',
            self::Expired => 'muted',
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
