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

use App\Entity\ShortUrl;
use App\Entity\User;
use Psr\Http\Message\UriInterface;

/**
 * A service for interacting with short URLs
 */
interface ShortUrlService
{
    /**
     * Returns a UriInterface for the given ShortUrl entity
     */
    public function buildUrl(ShortUrl $shortUrl): ?UriInterface;

    /**
     * Creates a ShortUrl entity for the given URL and user and (optionally) a custom slug
     */
    public function createShortUrl(string $url, User $user, ?string $customSlug = null): ShortUrl;

    /**
     * Updates a ShortUrl entity with the given user and (optionally) a custom slug
     */
    public function updateShortUrl(ShortUrl $shortUrl, User $user, ?string $customSlug = null): ShortUrl;

    /**
     * Soft-deletes a ShortUrl entity with the given user
     */
    public function softDeleteShortUrl(ShortUrl $shortUrl, User $user): ShortUrl;
}
