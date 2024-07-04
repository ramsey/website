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

use function array_replace;
use function preg_replace;

trait AnalyticsHelper
{
    /**
     * @param array<string, array<string, scalar> | scalar | null> | null $tags Additional tags to record with the event
     */
    private function getAnalyticsDetails(
        string $eventName,
        Request $request,
        Response $response,
        ?array $tags = null,
    ): AnalyticsDetails {
        $referrer = $request->headers->get('referer');

        $redirectUrl = $response->headers->get('location');
        if ($redirectUrl !== null) {
            // Replace the subsequent "://" in Archive.org redirect URIs to ensure proper parsing.
            $redirectUrl = preg_replace('#(?<!^)(https?)://#', '${1}%3A%2F%2F', $redirectUrl);
        }

        return new AnalyticsDetails(
            eventName: $eventName,
            ipAddress: (string) ($request->headers->get('do-connecting-ip') ?? $request->getClientIp()),
            userAgent: (string) $request->headers->get('user-agent'),
            url: $request->getUri(),
            referrer: $referrer,
            redirectUrl: $redirectUrl,
            tags: array_replace([
                'http_method' => $request->getMethod(),
                'http_referer' => $referrer,
                'status_code' => $response->getStatusCode(),
                'redirect_uri' => $redirectUrl,
            ], $tags ?? []),
        );
    }
}
