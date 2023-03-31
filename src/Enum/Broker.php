<?php

declare(strict_types=1);

namespace App\Enum;

enum Broker: string
{
    case TRADEREPUBLIC = 'traderepublic';

    public function label(): string
    {
        return match ($this) {
            self::TRADEREPUBLIC => 'Trade Republic',
        };
    }

    /**
     * Three-letter code
     *
     * @return string
     */
    public function shortName(): string
    {
        return match ($this) {
            self::TRADEREPUBLIC => 'trr',
        };
    }

    /**
     * @return array<Type>
     */
    public function supportedTypes(): array
    {
        $default = [
            Type::OTHER,
        ];

        return array_merge(
            match ($this) {
                self::TRADEREPUBLIC => [
                    Type::CRYPTO_TRADE,
                    Type::SECURITY_TRADE,
                    Type::PAYOUT,
                ],
            },
            $default
        );
    }
}
