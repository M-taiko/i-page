<?php

namespace App\Enums;

enum ColorScheme: string
{
    case NAVY = 'navy';
    case DARK = 'dark';
    case LIGHT = 'light';

    public function label(): string
    {
        return match ($this) {
            self::NAVY => 'Navy',
            self::DARK => 'Dark',
            self::LIGHT => 'Light',
        };
    }
}
