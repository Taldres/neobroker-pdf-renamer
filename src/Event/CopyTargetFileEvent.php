<?php

namespace App\Event;

use App\Model\File\TargetFile;
use Symfony\Contracts\EventDispatcher\Event;

class CopyTargetFileEvent extends Event
{
    public function __construct(
        public readonly TargetFile $targetFile,
    ) {
    }
}
