<?php

namespace App\Enums;

enum Type: string
{
    case STOCK_TRADE = 'stock_trade';
    case CRYPTO_TRADE = 'crypto_trade';
    case PAYOUT = 'payout';
    case OTHER = 'other';

    public function targetDirectory(): TargetDirectory
    {
        return match ($this) {
            self::STOCK_TRADE => TargetDirectory::TRADES_STOCK,
            self::CRYPTO_TRADE => TargetDirectory::TRADES_CRYPTO,
            self::PAYOUT => TargetDirectory::PAYOUTS,
            default => TargetDirectory::OTHERS,
        };
    }
}
