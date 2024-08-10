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

namespace App\Service\Entity;

use App\Entity\ShortUrl;
use App\Repository\ShortUrlRepository;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * A service for interacting with short URLs
 *
 * @extends EntityService<int, ShortUrl>
 */
interface ShortUrlService extends EntityService
{
    /**
     * Returns a UriInterface for the given ShortUrl entity
     */
    public function buildUrl(ShortUrl $shortUrl): ?UriInterface;

    /**
     * Checks whether the given custom slug is valid, and if so, sets it on the
     * short URL and returns the short URL
     *
     * @throws InvalidArgumentException if the custom slug is not valid
     */
    public function checkAndSetCustomSlug(ShortUrl $shortUrl, string $customSlug): ShortUrl;

    /**
     * Creates a ShortUrl entity and optionally sets a custom slug
     *
     * @throws InvalidArgumentException if the custom slug is not valid
     */
    public function createShortUrl(string $url, ?string $customSlug = null): ShortUrl;

    /**
     * Generates a random slug that may be used when creating a new short URL
     */
    public function generateSlug(): string;

    public function getRepository(): ShortUrlRepository;
}
