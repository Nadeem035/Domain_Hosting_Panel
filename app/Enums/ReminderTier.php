<?php

namespace App\Enums;

enum ReminderTier: string
{
    case Upcoming = 'upcoming';
    case DueSoon = 'due_soon';
    case Urgent = 'urgent';
    case Expired = 'expired';

    /**
     * Lower value = more urgent. Used for sorting widgets.
     */
    public function severity(): int
    {
        return match ($this) {
            self::Expired => 0,
            self::Urgent => 1,
            self::DueSoon => 2,
            self::Upcoming => 3,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Upcoming',
            self::DueSoon => 'Due soon',
            self::Urgent => 'Urgent',
            self::Expired => 'Expired',
        };
    }
}