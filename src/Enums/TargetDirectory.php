<?php

namespace App\Enums;

enum TargetDirectory: string
{
    case TRADES = 'trades';
    case TRADES_STOCK = 'trades_stock';
    case TRADES_CRYPTO = 'trades_crypto';
    case PAYOUTS = 'payouts';
    case OTHERS = 'others';

    public function parentDirectory(): ?TargetDirectory
    {
        return match ($this) {
            self::TRADES_STOCK,
            self::TRADES_CRYPTO => self::TRADES,
            default => null,
        };
    }
}
