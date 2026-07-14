<?php

namespace App\Enums;

enum ReactionType: string
{
    case LIKE = 'like';
    case LOVE = 'love';

    public function label(): string
    {
        return match ($this) {
            self::LIKE => 'Like',
            self::LOVE => 'Love',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::LIKE => '👍',
            self::LOVE => '❤️',
        };
    }
}
