<?php

declare(strict_types=1);

namespace App\Exception\Filesystem;

use Exception;

class PathNotReadableException extends Exception
{
    public function __construct(string $path, string $message = '', int $code = 0)
    {
        $message = !empty($message) ? $message : "Path '{$path}' does not exist or is not readable.";
        $code = $code !== 0 ? $code : 1680288279559;

        parent::__construct($message, $code);
    }
}
