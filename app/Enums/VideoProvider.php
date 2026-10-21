<?php

namespace App\Enums;

enum VideoProvider: string
{
    case Bunny = 'Bunny';
    case YouTube = 'YouTube';
    case Vimeo = 'Vimeo';
    case External = 'External';

    public function label(): string
    {
        return match ($this) {
            self::Bunny => 'Bunny Stream',
            self::YouTube => 'YouTube',
            self::Vimeo => 'Vimeo',
            self::External => 'External HLS',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Bunny => 'accent',
            self::YouTube => 'danger',
            self::Vimeo => 'info',
            self::External => 'muted',
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
