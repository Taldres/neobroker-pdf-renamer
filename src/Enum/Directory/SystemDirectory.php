<?php

declare(strict_types=1);

namespace App\Enum\Directory;

enum SystemDirectory: string
{
    case SOURCE = 'input';
    case TARGET = 'output';
    case TRANSLATIONS = 'translations';

    public function path(): string
    {
        return "/" . $this->value;
    }

    public function dirname(): string
    {
        return $this->value;
    }
}
