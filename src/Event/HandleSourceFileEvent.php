<?php

namespace App\Event;

use App\Model\File\SourceFile;
use Symfony\Contracts\EventDispatcher\Event;

class HandleSourceFileEvent extends Event
{
    public function __construct(
        public readonly SourceFile $sourceFile,
    ) {
    }
}
