<?php

declare(strict_types=1);

namespace App\Model\File;

use App\Enum\Type;

class TargetFile extends File
{
    public function __construct(
        public string $path,
        public string $filename,
        public SourceFile $sourceFile,
        public Type $type,
        public string $code,
        public string $date,
    ) {
        parent::__construct($this->path, $this->filename);
    }
}
