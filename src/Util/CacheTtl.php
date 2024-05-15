<?php

declare(strict_types=1);

namespace App\Util;

enum CacheTtl: int
{
    case Week = 604_800;
    case Day = 86_400;
    case Hour = 3_600;
}
