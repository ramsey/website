<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Service\Analytics;

use Devarts\PlausiblePHP\PlausibleAPI;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function in_array;

/**
 * A service for interacting with the Plausible API to record analytics events
 */
final readonly class Plausible implements AnalyticsService
{
    use AnalyticsHelper;

    /**
     * @param list<string> $domains
     */
    public function __construct(
        private PlausibleAPI $plausibleApi,
        #[Autowire('%app.service.plausible.domains%')] private array $domains,
    ) {
    }

    public function recordEvent(string $eventName, Request $request, Response $response, ?array $tags = null): void
    {
        if (!in_array($request->getHost(), $this->domains)) {
            throw new UnknownAnalyticsDomain("{$request->getHost()} is not a valid analytics domain");
        }

        $details = $this->getAnalyticsDetails($eventName, $request, $response, $tags);

        /** @var array{currency: string, amount: float | string} | null $revenue */
        $revenue = $details->tags['revenue'] ?? null;

        /** @var array<string, scalar | null> $tagsWithoutRevenue */
        $tagsWithoutRevenue = $details->tags;
        unset($tagsWithoutRevenue['revenue']);

        $this->plausibleApi->recordEvent(
            site_id: $request->getHost(),
            event_name: $details->eventName,
            url: $details->url,
            user_agent: $details->userAgent,
            ip_address: $details->ipAddress,
            referrer: $details->referrer,
            properties: $tagsWithoutRevenue,
            revenue: $revenue,
        );
    }
}
