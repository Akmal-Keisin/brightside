<?php

namespace App\Enums;

enum ContentType: string
{
    case Article = 'Article';
    case Video = 'Video';
    case Quiz = 'Quiz';

    public function label(): string
    {
        return match ($this) {
            self::Article => 'Article',
            self::Video => 'Video',
            self::Quiz => 'Quiz',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Article => 'info',
            self::Video => 'accent',
            self::Quiz => 'success',
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
