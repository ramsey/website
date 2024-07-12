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
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
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
    use AnalyticsHelper;

    /**
     * @param list<string> $domains
     */
    public function __construct(
        private PlausibleAPI $plausibleApi,
        #[Autowire('%app.service.plausible.domains%')] private array $domains,
        private AnalyticsDetailsFactory $analyticsDetailsFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function recordEventFromWebContext(
        string $eventName,
        Request $request,
        Response $response,
        ?array $tags = null,
    ): void {
        if ($this->skipPath($request->getRequestUri())) {
            return;
        }

        $details = $this->analyticsDetailsFactory->createFromWebContext($eventName, $request, $response, $tags);
        $this->recordEventFromDetails($details);
    }

    public function recordEventFromDetails(AnalyticsDetails $details): void
    {
        if ($this->skipPath($details->url->getPath())) {
            return;
        }

        if (!in_array($details->url->getHost(), $this->domains)) {
            throw new UnknownAnalyticsDomain("{$details->url->getHost()} is not a valid analytics domain");
        }

        $properties = array_replace([
            'http_referer' => $details->referrer?->__toString(),
            'redirect_uri' => $details->redirectUrl?->__toString(),
        ], $details->tags ?? []);

        try {
            $this->plausibleApi->recordEvent(
                site_id: $details->url->getHost(),
                event_name: $details->eventName,
                url: $details->url->__toString(),
                user_agent: $details->userAgent,
                ip_address: $details->ipAddress,
                referrer: $details->referrer?->__toString(),
                properties: $properties,
            );
        } catch (ClientExceptionInterface $exception) {
            $this->logger->error('Unable to send analytics to Plausible: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
