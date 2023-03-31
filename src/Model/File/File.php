<?php

declare(strict_types=1);

namespace App\Model\File;

class File
{
    public function __construct(
        public string $path,
        public string $filename,
    ) {
    }
}
