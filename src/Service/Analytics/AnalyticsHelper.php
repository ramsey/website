<?php

declare(strict_types=1);

namespace App\Service\Analytics;

use function str_starts_with;

trait AnalyticsHelper
{
    private function skipPath(string $path): bool
    {
        return str_starts_with($path, '/health');
    }
}
