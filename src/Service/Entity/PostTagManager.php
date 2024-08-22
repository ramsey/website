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

use App\Entity\PostTag;
use App\Repository\PostTagRepository;
use InvalidArgumentException;

use function preg_match;

final readonly class PostTagManager implements PostTagService
{
    private const string TAG_PATTERN = '/^[a-zA-Z0-9\-_.:+]{1,50}$/';

    public function __construct(private PostTagRepository $repository)
    {
    }

    public function createTag(string $tag): PostTag
    {
        $this->checkTagName($tag);

        return (new PostTag())->setName($tag);
    }

    public function getRepository(): PostTagRepository
    {
        return $this->repository;
    }

    private function checkTagName(string $name): void
    {
        if (!preg_match(self::TAG_PATTERN, $name)) {
            throw new InvalidArgumentException("Tag name is invalid: $name");
        }
    }
}
