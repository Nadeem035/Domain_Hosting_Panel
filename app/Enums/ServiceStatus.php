<?php

namespace App\Enums;

enum ServiceStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case PendingRenewal = 'pending_renewal';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
            self::PendingRenewal => 'Pending renewal',
        };
    }
}