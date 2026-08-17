<?php

namespace App\Enums;

enum PanelType: string
{
    case Hosting = 'hosting';
    case Domain = 'domain';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Hosting => 'Hosting',
            self::Domain => 'Domain',
            self::Both => 'Hosting & Domain',
        };
    }
}