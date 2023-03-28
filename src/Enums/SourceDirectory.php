<?php

namespace App\Enums;

enum SourceDirectory: string
{
    case BILLING = 'billing';

    public function folder(): string
    {
        return match ($this) {
            self::BILLING => 'Abrechnung',
        };
    }
}
