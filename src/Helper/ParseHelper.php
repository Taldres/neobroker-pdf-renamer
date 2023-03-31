<?php

declare(strict_types=1);

namespace App\Helper;

use App\Enum\Type;

class ParseHelper
{
    /**
     * Parses the text and returns the ISIN or null
     *
     * @param string $text
     *
     * @return string|null
     */
    public static function parseIsin(string $text): ?string
    {
        $pregMatch = preg_match("/ISIN:\s*([A-Z]{2}[A-Z0-9]{9}[0-9])/m", $text, $matches);

        if ((bool) $pregMatch === false || count($matches) < 2) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Parses the text  and returns the date or null
     *
     * @param string $text
     *
     * @return string|null
     */
    public static function parseDate(string $text): ?string
    {
        $pregMatch = preg_match(
            "/DATUM\s*((3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2}))/m",
            $text,
            $matches
        );

        if ((bool) $pregMatch === false || count($matches) < 2) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Returns the abbreviation of a cryptocurrency
     *
     * @param string $text
     *
     * @return string|null
     */
    public static function parseCryptoAbbreviation(string $text): ?string
    {
        $pregMatch = preg_match("/([[:alnum:][:blank:]*]+)\s\(([A-Z]{3,5})\)/m", $text, $matches);

        if ((bool) $pregMatch === false || count($matches) < 2) {
            return null;
        }

        return $matches[2];
    }

    /**
     * Returns the code of the asset or cryptocurrency
     *
     * @param string $text
     * @param Type $type
     *
     * @return string|null
     */
    public static function parseCode(string $text, Type $type): ?string
    {
        return match ($type) {
            Type::CRYPTO_TRADE => self::parseCryptoAbbreviation($text),
            default => self::parseIsin($text)
        };
    }

    /**
     * Checks if the indicator does exist
     *
     * @param string $text
     * @param string $indicator
     *
     * @return bool
     */
    public static function checkIndicator(string $text, string $indicator): bool
    {
        $pregMatch = preg_match("/(?:" . strtoupper($indicator) . ")/m", $text, $matches);

        return (bool) $pregMatch !== false && count($matches) > 0;
    }
}
