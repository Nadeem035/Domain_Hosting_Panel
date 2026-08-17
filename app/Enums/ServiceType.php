<?php

namespace App\Enums;

enum ServiceType: string
{
    case Domain = 'domain';
    case Hosting = 'hosting';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Domain => 'Domain',
            self::Hosting => 'Hosting',
            self::Both => 'Domain + Hosting',
        };
    }

    public function involvesHosting(): bool
    {
        return $this === self::Hosting || $this === self::Both;
    }

    public function involvesDomain(): bool
    {
        return $this === self::Domain || $this === self::Both;
    }
}