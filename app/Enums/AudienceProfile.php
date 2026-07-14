<?php

namespace App\Enums;

enum AudienceProfile: string
{
    case BUSINESS = 'business';
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    public function label(): string
    {
        return match ($this) {
            self::BUSINESS => 'Business',
            self::PUBLIC => 'Public',
            self::PRIVATE => 'Private',
        };
    }
}
