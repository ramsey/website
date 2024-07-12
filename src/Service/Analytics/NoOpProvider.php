<?php

declare(strict_types=1);

namespace App\Service\Analytics;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class NoOpProvider implements AnalyticsService
{
    public function recordEventFromWebContext(
        string $eventName,
        Request $request,
        Response $response,
        ?array $tags = null,
    ): void {
    }

    public function recordEventFromDetails(AnalyticsDetails $details): void
    {
    }
}
