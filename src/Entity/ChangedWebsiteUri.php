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

namespace App\Entity;

use Psr\Http\Message\UriInterface;

/**
 * Represents a website URI that has changed.
 *
 * This URI might redirect to a new URI, or it might have gone away or is no
 * longer found. Its current state is indicated by the `httpStatusCode`
 * property, and if the status code indicates the URI redirects somewhere else,
 * it should have a `redirectUri` property.
 */
final readonly class ChangedWebsiteUri
{
    public function __construct(
        public UriInterface $uri,
        public int $httpStatusCode,
        public ?UriInterface $redirectUri = null,
    ) {
    }
}
