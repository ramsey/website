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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class MultiProvider implements AnalyticsService
{
    /**
     * @var array<AnalyticsService>
     */
    private array $providers;

    public function __construct(AnalyticsService ...$providers)
    {
        $this->providers = $providers;
    }

    public function recordEvent(string $eventName, Request $request, Response $response, ?array $tags = null): void
    {
        foreach ($this->providers as $provider) {
            try {
                $provider->recordEvent($eventName, $request, $response, $tags);
            } catch (UnknownAnalyticsDomain) {
                // Ignore exception.
            }
        }
    }
}