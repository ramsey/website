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

use App\Service\AnalyticsEventService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A service for recording analytics events in the application database
 */
final readonly class AppDatabase implements AnalyticsService
{
    public function __construct(
        private AnalyticsEventService $analyticsEventService,
        private EntityManagerInterface $entityManager,
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
        $details = $this->analyticsDetailsFactory->createFromWebContext($eventName, $request, $response, $tags);
        $this->recordEventFromDetails($details);
    }

    public function recordEventFromDetails(AnalyticsDetails $details): void
    {
        try {
            $this->entityManager->wrapInTransaction(
                function (EntityManagerInterface $entityManager) use ($details): void {
                    $event = $this->analyticsEventService->createAnalyticsEventFromDetails($details);
                    $entityManager->persist($event->getDevice());
                    $entityManager->persist($event);
                    $entityManager->flush();
                },
            );
        } catch (ORMException $exception) {
            $this->logger->error('Unable to write analytics to the database: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
