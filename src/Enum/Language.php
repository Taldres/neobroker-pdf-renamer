<?php

declare(strict_types=1);

namespace App\Enum;

enum Language: string
{
    case DE = 'de';

    public function label(): string
    {
        return match ($this) {
            self::DE => 'German',
        };
    }
}
