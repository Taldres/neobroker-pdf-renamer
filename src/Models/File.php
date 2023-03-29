<?php

namespace App\Models;

use App\Enums\Type;

readonly class File
{
    public function __construct(
        public string $code,
        public string $date,
        public Type $type,
    ) {
    }
}
