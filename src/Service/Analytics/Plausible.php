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

use function array_replace;
use function in_array;

/**
 * A service for interacting with the Plausible API to record analytics events
 */
final readonly class Plausible implements AnalyticsService
{
    /**
     * @param list<string> $domains
     */
    public function __construct(
        private PlausibleAPI $plausibleApi,
        #[Autowire('%app.service.plausible.domains%')] private array $domains,
    ) {
    }

    public function recordEvent(
        string $eventName,
        Request $request,
        Response $response,
        ?array $properties = null,
    ): void {
        if (!in_array($request->getHost(), $this->domains)) {
            return;
        }

        $properties = array_replace([
            'http_method' => $request->getMethod(),
            'status_code' => $response->getStatusCode(),
            'redirect_uri' => $response->headers->get('location'),
        ], $properties ?? []);

        /** @var array{currency: string, amount: float | string} | null $revenue */
        $revenue = $properties['revenue'] ?? null;
        unset($properties['revenue']);

        /** @var array<string, scalar | null> $propertiesWithoutRevenue */
        $propertiesWithoutRevenue = $properties;

        $this->plausibleApi->recordEvent(
            site_id: $request->getHost(),
            event_name: $eventName,
            url: $request->getUri(),
            user_agent: (string) $request->headers->get('user-agent'),
            ip_address: (string) $request->getClientIp(),
            referrer: $request->headers->get('referer'),
            properties: $propertiesWithoutRevenue,
            revenue: $revenue,
        );
    }
}
