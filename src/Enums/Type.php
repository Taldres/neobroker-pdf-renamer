<?php

namespace App\Enums;

enum Type: string
{
    case SECURITY_TRADE = 'security_trade';
    case CRYPTO_TRADE = 'crypto_trade';
    case PAYOUT = 'payout';
    case OTHER = 'other';

    public function targetDirectory(): TargetDirectory
    {
        return match ($this) {
            self::SECURITY_TRADE => TargetDirectory::TRADES_SECURITY,
            self::CRYPTO_TRADE => TargetDirectory::TRADES_CRYPTO,
            self::PAYOUT => TargetDirectory::PAYOUTS,
            default => TargetDirectory::OTHERS,
        };
    }
}
