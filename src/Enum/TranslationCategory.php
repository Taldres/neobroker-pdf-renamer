<?php

declare(strict_types=1);

namespace App\Enum;

enum TranslationCategory: string
{
    case INDICATORS = 'indicators';
    case TARGET_DIRECTORIES = 'target_directories';
}
