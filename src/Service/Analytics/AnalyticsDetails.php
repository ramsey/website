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

use Psr\Http\Message\UriInterface;

final readonly class AnalyticsDetails
{
    /**
     * @param array<string, scalar> $serverEnvironment
     * @param array<string, scalar | null> $tags Additional tags to record with the event
     */
    public function __construct(
        public string $eventName,
        public UriInterface $url,
        public ?string $geoCity = null,
        public ?string $geoCountryCode = null,
        public ?float $geoLatitude = null,
        public ?float $geoLongitude = null,
        public ?string $geoSubdivisionCode = null,
        public string $ipAddress = '',
        public string $ipAddressUserAgentHash = '',
        public ?string $locale = null,
        public ?UriInterface $redirectUrl = null,
        public ?UriInterface $referrer = null,
        public array $serverEnvironment = [],
        public array $tags = [],
        public string $userAgent = '',
    ) {
    }
}
