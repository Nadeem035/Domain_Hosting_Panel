<?php

namespace App\Enums;

enum PanelType: string
{
    case Cpanel = 'cpanel';
    case Whm = 'whm';
    case Plesk = 'plesk';
    case DirectAdmin = 'directadmin';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cpanel => 'cPanel',
            self::Whm => 'WHM',
            self::Plesk => 'Plesk',
            self::DirectAdmin => 'DirectAdmin',
            self::Other => 'Other',
        };
    }
}