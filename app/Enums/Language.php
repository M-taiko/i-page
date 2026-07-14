<?php

namespace App\Enums;

enum Language: string
{
    case ENGLISH = 'en';
    case ARABIC = 'ar';
    case FRENCH = 'fr';

    public function label(): string
    {
        return match ($this) {
            self::ENGLISH => 'English',
            self::ARABIC => 'العربية',
            self::FRENCH => 'Français',
        };
    }

    public function locale(): string
    {
        return $this->value;
    }
}
