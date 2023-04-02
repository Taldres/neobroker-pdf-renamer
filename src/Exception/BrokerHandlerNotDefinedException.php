<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class BrokerHandlerNotDefinedException extends Exception
{
    public function __construct(string $message = '', int $code = 0)
    {
        $message = !empty($message) ? $message : 'No broker handler has been defined.';
        $code    = $code !== 0 ? $code : 1680368415857;

        parent::__construct($message, $code);
    }
}
