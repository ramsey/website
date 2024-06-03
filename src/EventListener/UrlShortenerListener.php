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

use App\Controller\ShortUrlController;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use function str_starts_with;
use function strtolower;

#[AsEventListener(event: 'kernel.request', priority: 250)]
final readonly class UrlShortenerListener
{
    public function __invoke(RequestEvent $event): void
    {
        $host = strtolower($event->getRequest()->getHost());
        $path = $event->getRequest()->getRequestUri();

        if ($host === 'bram.se' || str_starts_with($path, '/su/')) {
            $event->getRequest()->attributes->set('_controller', ShortUrlController::class);
        }
    }
}
