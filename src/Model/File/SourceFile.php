<?php

declare(strict_types=1);

namespace App\Model\File;

class SourceFile extends File
{
    public function __construct(
        public string $path,
        public string $filename,
    ) {
        parent::__construct($this->path, $this->filename);
    }
}
