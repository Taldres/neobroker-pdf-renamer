<?php

declare(strict_types=1);

namespace App\Exception\Translation;

use App\Enum\Language;
use Exception;

class LanguageFileNotFoundException extends Exception
{
    public function __construct(Language $language, string $message = '', int $code = 0)
    {
        $message = !empty($message) ? $message : "Language file for '{$language->value}' does not exist.";
        $code = $code !== 0 ? $code : 1680287581402;

        parent::__construct($message, $code);
    }
}
