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

use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Model\City;
use GeoIp2\ProviderInterface;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_replace;
use function hash_hmac;
use function preg_replace;

final readonly class StandardAnalyticsDetailsFactory implements AnalyticsDetailsFactory
{
    public function __construct(
        #[Autowire('%app.service.analytics.secret_key%')] private string $analyticsSecretKey,
        private ProviderInterface $geoIpReader,
        private UriFactoryInterface $uriFactory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, scalar | null> | null $tags Additional tags to record with the event
     */
    public function createFromWebContext(
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

        $ipAddress = (string) ($request->headers->get('do-connecting-ip') ?? $request->getClientIp());
        $userAgent = (string) $request->headers->get('user-agent');
        $hash = hash_hmac('ripemd160', $ipAddress . $userAgent, $this->analyticsSecretKey, true);

        $geoIpCity = $this->getGeoIpCity($ipAddress);

        $tags = array_replace([
            'http_method' => $request->getMethod(),
            'status_code' => $response->getStatusCode(),
        ], $tags ?? []);

        return new AnalyticsDetails(
            eventName: $eventName,
            url: $this->uriFactory->createUri($request->getUri()),
            geoCity: $geoIpCity?->city->name,
            geoCountryCode: $geoIpCity?->country?->isoCode,
            geoLatitude: $geoIpCity?->location?->latitude,
            geoLongitude: $geoIpCity?->location?->longitude,
            geoSubdivisionCode: $geoIpCity?->mostSpecificSubdivision?->isoCode,
            ipAddress: $ipAddress,
            ipAddressUserAgentHash: $hash,
            locale: $request->getLocale(),
            redirectUrl: $redirectUrl ? $this->uriFactory->createUri($redirectUrl) : null,
            referrer: $referrer ? $this->uriFactory->createUri($referrer) : null,
            serverEnvironment: $request->server->all(),
            tags: $tags,
            userAgent: $userAgent,
        );
    }

    private function getGeoIpCity(string $ipAddress): ?City
    {
        try {
            return $this->geoIpReader->city($ipAddress);
        } catch (InvalidDatabaseException $exception) {
            $this->logger->error('Unable to read from geo IP database: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        } catch (AddressNotFoundException) {
            return null;
        }
    }
}
