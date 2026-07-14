<?php

namespace App\Enums;

enum PostAudience: string
{
    case ALL = 'all';
    case IN_HOUSE = 'in_house';
    case TEAM = 'team';
    case CHANNEL = 'channel';

    public function label(): string
    {
        return match ($this) {
            self::ALL => 'All',
            self::IN_HOUSE => 'In-House Guests',
            self::TEAM => 'Team',
            self::CHANNEL => 'Channel',
        };
    }
}
