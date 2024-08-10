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

namespace App\Service\Blog;

use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostStatus;
use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @phpstan-type PostMetadata array{
 *      id?: string,
 *      authors?: list<string> | string,
 *      title?: string,
 *      slug?: string,
 *      status?: 'deleted' | 'draft' | 'hidden' | 'published',
 *      categories?: list<string>,
 *      tags?: list<string>,
 *      description?: string,
 *      keywords?: list<string>,
 *      excerpt?: string,
 *      feed_id?: string,
 *      date?: string,
 *      updated?: string,
 *  }
 */
final readonly class ParsedPostMetadata
{
    /**
     * @param list<PostCategory> $categories
     * @param list<string> $tags
     * @param list<string> $keywords
     * @param array<string, mixed[] | scalar | null> $additional Additional metadata
     */
    public function __construct(
        public UuidInterface $id,
        public PostBodyType $contentType,
        public string $title,
        public string $slug,
        public PostStatus $status,
        public array $categories,
        public array $tags,
        public ?string $description,
        public array $keywords,
        public ?string $excerpt,
        public ?string $feedId,
        public array $additional,
        public DateTimeInterface $createdAt,
        public ?DateTimeInterface $updatedAt,
    ) {
    }
}
