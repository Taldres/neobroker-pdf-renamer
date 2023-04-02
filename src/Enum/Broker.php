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
                    Type::DIVIDENDS,
                ],
            },
            $default
        );
    }

    /**
     * @return array<Language>
     */
    public function supportedLanguages(): array
    {
        return match ($this) {
            self::TRADEREPUBLIC => [
                Language::DE,
            ],
        };
    }
}
