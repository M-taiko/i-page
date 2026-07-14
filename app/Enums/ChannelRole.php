<?php

namespace App\Enums;

enum ChannelRole: string
{
    case MEMBER = 'member';
    case MODERATOR = 'moderator';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::MEMBER => 'Member',
            self::MODERATOR => 'Moderator',
            self::ADMIN => 'Admin',
        };
    }
}
