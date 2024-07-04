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

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[AsController]
#[Route('/health', 'app_health')]
final readonly class HealthController
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $this->connection->fetchNumeric('SELECT 1');
        } catch (Throwable $throwable) {
            $this->logger->alert('Health check failed; {class}: {message}', [
                'class' => $throwable::class,
                'message' => $throwable->getMessage(),
            ]);

            return new Response('not ready', Response::HTTP_SERVICE_UNAVAILABLE, [
                'content-type' => 'text/plain',
                'retry-after' => 10,
            ]);
        }

        return new Response('ok', Response::HTTP_OK, [
            'content-type' => 'text/plain',
        ]);
    }
}
