<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class CommandCancelledException extends RuntimeException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message, 1680169327460);
    }
}
