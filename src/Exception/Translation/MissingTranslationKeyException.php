<?php

declare(strict_types=1);

namespace App\Exception\Translation;

use Exception;

class MissingTranslationKeyException extends Exception
{
    public function __construct(string $missingKey, string $message = '', int $code = 0)
    {
        $message = !empty($message) ? $message : "Invalid translation! Missing key: {$missingKey}";
        $code    = $code !== 0 ? $code : 1680287581402;

        parent::__construct($message, $code);
    }
}
