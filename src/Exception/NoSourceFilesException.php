<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class NoSourceFilesException extends Exception
{
    public function __construct(string $message = '', int $code = 0)
    {
        $message = !empty($message) ? $message : 'No files could be found in the source folder to check.';
        $code    = $code !== 0 ? $code : 1680169327460;

        parent::__construct($message, $code);
    }
}
