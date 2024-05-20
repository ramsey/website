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

use function base64_decode;

/**
 * Represents an OpenPGP Web Key as might be fetched
 * from an OpenPGP Web Key Directory.
 *
 * @link https://datatracker.ietf.org/doc/draft-koch-openpgp-webkey-service/ OpenPGP Web Key Directory
 */
final readonly class OpenPgpWebKey
{
    public function __construct(
        public string $hostname,
        public string $localPart,
        public string $base64EncodedKey,
    ) {
    }

    public function getRawBinaryKey(): string
    {
        return base64_decode($this->base64EncodedKey);
    }
}
