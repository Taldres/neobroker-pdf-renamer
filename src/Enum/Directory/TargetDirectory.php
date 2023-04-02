<?php

declare(strict_types=1);

namespace App\Enum\Directory;

enum TargetDirectory: string
{
    case TRADES = 'trades';
    case TRADES_SECURITY = 'trades_security';
    case TRADES_CRYPTO = 'trades_crypto';
    case DIVIDENDS = 'dividends';
    case OTHERS = 'others';

    public function parentDirectory(): ?TargetDirectory
    {
        return match ($this) {
            self::TRADES_SECURITY,
            self::TRADES_CRYPTO => self::TRADES,
            default => null,
        };
    }
}
