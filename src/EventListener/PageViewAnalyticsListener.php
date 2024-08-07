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

use App\Service\Analytics\AnalyticsService;
use App\Service\Analytics\UnknownAnalyticsDomain;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

#[AsEventListener(event: 'kernel.terminate', priority: 20)]
final readonly class PageViewAnalyticsListener
{
    public function __construct(private AnalyticsService $analytics)
    {
    }

    public function __invoke(TerminateEvent $event): void
    {
        try {
            $this->analytics->recordEventFromWebContext('pageview', $event->getRequest(), $event->getResponse());
        } catch (UnknownAnalyticsDomain) {
            // Ignore the exception.
        }
    }
}
