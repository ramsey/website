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

namespace App\EventListener;

use App\Service\Analytics\AnalyticsDetailsFactory;
use App\Service\Device\DeviceService;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

use function bin2hex;
use function number_format;
use function sprintf;

#[AsEventListener(event: 'kernel.terminate', priority: -1)]
final readonly class RequestLogListener
{
    /**
     * @param LoggerInterface $appHealthLogger Dedicated log channel for requests to /health
     * @param LoggerInterface $appRequestLogger Dedicated log channel for all app requests
     */
    public function __construct(
        private AnalyticsDetailsFactory $analyticsDetailsFactory,
        private LoggerInterface $appHealthLogger,
        private LoggerInterface $appRequestLogger,
        private DeviceService $deviceService,
        private ClockInterface $monotonicClock,
    ) {
    }

    public function __invoke(TerminateEvent $event): void
    {
        $method = $event->getRequest()->getMethod();
        $statusCode = $event->getResponse()->getStatusCode();
        $requestUri = $event->getRequest()->getRequestUri();

        if ($requestUri === '/health' && $statusCode === Response::HTTP_SERVICE_UNAVAILABLE) {
            return;
        }

        $details = $this->analyticsDetailsFactory->createFromWebContext(
            'request_complete',
            $event->getRequest(),
            $event->getResponse(),
        );

        $device = $this->deviceService->getDevice($details->userAgent, $details->serverEnvironment);

        $logger = match ($requestUri) {
            '/health' => $this->appHealthLogger,
            default => $this->appRequestLogger,
        };

        /** @var float | string | null $execTime */
        $execTime = $event->getRequest()->server->get('REQUEST_TIME_FLOAT');

        if ($execTime !== null) {
            $clockTime = (float) $this->monotonicClock->now()->format('U.u');
            $execTime = number_format($clockTime - (float) $execTime, 6, '.', '');
        }

        $logger->info(sprintf('Responded %d for %s %s', $statusCode, $method, $details->url), [
            'exec_time' => $execTime,
            'device' => [
                'category' => $device->getCategory(),
                'family' => $device->getFamily(),
                'name' => $device->getName(),
                'os' => $device->getOsFamily(),
                'type' => $device->getDevice(),
            ],
            'geo' => [
                'city' => $details->geoCity,
                'country_code' => $details->geoCountryCode,
                'latitude' => $details->geoLatitude,
                'longitude' => $details->geoLongitude,
                'subdivision_code' => $details->geoSubdivisionCode,
            ],
            'host' => $details->url->getHost(),
            'ip' => $details->ipAddress,
            'redirect_url' => $details->redirectUrl?->__toString(),
            'referrer' => $details->referrer?->__toString(),
            'request_method' => $method,
            'status_code' => $statusCode,
            'url' => $details->url->__toString(),
            'user_agent' => $details->userAgent,
            'visitor_hash' => bin2hex($details->ipAddressUserAgentHash),
        ]);
    }
}
