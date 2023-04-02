<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\Broker;
use App\Enum\Language;
use Exception;

class LanguageNotSupportedByBrokerException extends Exception
{
    public function __construct(Language $language, Broker $broker, string $message = '', int $code = 0)
    {
        $message = !empty($message) ? $message : "{$language->label()} is not supported for broker {$broker->label()}.";
        $code    = $code !== 0 ? $code : 1680439099331;

        parent::__construct($message, $code);
    }
}
