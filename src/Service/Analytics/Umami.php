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

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function array_filter;

final readonly class Umami implements AnalyticsService
{
    use AnalyticsHelper;

    /**
     * @param list<array{domain: string, website_id: string}> $domains
     */
    public function __construct(
        #[Autowire('%app.service.umami.api_token%')] private string $umamiApiKey,
        #[Autowire('%app.service.umami.base_uri%')] private string $umamiBaseUri,
        #[Autowire('%app.service.umami.domains%')] private array $domains,
        private HttpClientInterface $httpClient,
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

        $websiteId = $this->getWebsiteId($details->url->getHost());

        try {
            $this->httpClient->request('POST', $this->umamiBaseUri . 'send', [
                'headers' => array_filter([
                    'user-agent' => $details->userAgent,
                    'x-forwarded-for' => $details->ipAddress,
                    'x-umami-api-key' => $this->umamiApiKey,
                ]),
                'json' => [
                    'type' => 'event',
                    'payload' => array_filter([
                        'hostname' => $details->url->getHost(),
                        'language' => $details->locale,
                        'referrer' => $details->referrer?->__toString(),
                        'screen' => '',
                        'title' => '',
                        'url' => $details->url->__toString(),
                        'website' => $websiteId,
                        'name' => 'pageview',
                        'data' => $details->tags,
                    ]),
                ],
            ]);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Unable to send analytics to Umami: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function getWebsiteId(string $hostName): string
    {
        foreach ($this->domains as $domain) {
            if ($hostName === $domain['domain']) {
                return $domain['website_id'];
            }
        }

        throw new UnknownAnalyticsDomain("$hostName is not a valid analytics domain");
    }
}
