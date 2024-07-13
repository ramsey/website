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

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

use function number_format;

#[AsEventListener(event: 'kernel.terminate', priority: -1)]
final readonly class RequestLogListener
{
    public function __construct(
        private LoggerInterface $logger,
        #[Target('monotonicClock')] private ClockInterface $clock,
    ) {
    }

    public function __invoke(TerminateEvent $event): void
    {
        /** @var float | string | null $execTime */
        $execTime = $event->getRequest()->server->get('REQUEST_TIME_FLOAT');

        if ($execTime !== null) {
            $clockTime = (float) $this->clock->now()->format('U.u');
            $execTime = number_format($clockTime - (float) $execTime, 6, '.', '');
        }

        $this->logger->info('Request for ' . $event->getRequest()->getUri(), [
            'url' => $event->getRequest()->getUri(),
            'request' => [
                'method' => $event->getRequest()->getMethod(),
                'headers' => $event->getRequest()->headers->all(),
            ],
            'response' => [
                'code' => $event->getResponse()->getStatusCode(),
                'headers' => $event->getResponse()->headers->all(),
            ],
            'exec_time' => $execTime,
        ]);
    }
}
