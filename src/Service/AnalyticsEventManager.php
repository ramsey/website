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

namespace App\Service;

use App\Entity\AnalyticsEvent;
use App\Service\Analytics\AnalyticsDetails;
use DateTimeImmutable;

/**
 * Manages creation of analytics events
 */
final readonly class AnalyticsEventManager implements AnalyticsEventService
{
    public function __construct(
        private AnalyticsDeviceService $deviceService,
    ) {
    }

    public function createAnalyticsEventFromDetails(AnalyticsDetails $details): AnalyticsEvent
    {
        return (new AnalyticsEvent())
            ->setDevice($this->deviceService->getDevice($details->serverEnvironment))
            ->setGeoCity($details->geoCity)
            ->setGeoCountryCode($details->geoCountryCode)
            ->setGeoLatitude($details->geoLatitude)
            ->setGeoLongitude($details->geoLongitude)
            ->setGeoSubdivisionCode($details->geoSubdivisionCode)
            ->setHostname($details->url->getHost())
            ->setIpUserAgentHash($details->ipAddressUserAgentHash)
            ->setLocale($details->locale)
            ->setName($details->eventName)
            ->setRedirectUrl($details->redirectUrl?->__toString())
            ->setReferrer($details->referrer?->__toString())
            ->setTags($details->tags)
            ->setUri($details->url->__toString())
            ->setUserAgent($details->userAgent)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());
    }
}
