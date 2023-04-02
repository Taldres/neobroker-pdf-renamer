<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Directory\TargetDirectory;

enum Type: string
{
    case SECURITY_TRADE = 'security_trade';
    case CRYPTO_TRADE = 'crypto_trade';
    case DIVIDENDS = 'dividends';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SECURITY_TRADE => 'Security Trade',
            self::CRYPTO_TRADE => 'Crypto Trade',
            self::DIVIDENDS => 'Dividends',
            default => 'Other',
        };
    }

    public function targetDirectory(): TargetDirectory
    {
        return match ($this) {
            self::SECURITY_TRADE => TargetDirectory::TRADES_SECURITY,
            self::CRYPTO_TRADE => TargetDirectory::TRADES_CRYPTO,
            self::DIVIDENDS => TargetDirectory::DIVIDENDS,
            default => TargetDirectory::OTHERS,
        };
    }
}
