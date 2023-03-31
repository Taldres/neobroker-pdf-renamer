<?php

declare(strict_types=1);

namespace App\Exception\Filesystem;

use Exception;

class PathNotWritableException extends Exception
{
    public function __construct(string $path, string $message = '', int $code = 0)
    {
        $message = !empty($message) ? $message : "Path '{$path}' does not exist or is not writable.";
        $code = $code !== 0 ? $code : 1680288461901;

        parent::__construct($message, $code);
    }
}
