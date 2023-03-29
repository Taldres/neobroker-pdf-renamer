<?php

namespace App\Enums;

enum TargetDirectory: string
{
    case TRADES = 'trades';
    case TRADES_SECURITY = 'trades_security';
    case TRADES_CRYPTO = 'trades_crypto';
    case PAYOUTS = 'payouts';
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
