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

use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\User;
use App\Repository\PostRepository;
use DateTimeImmutable;

final readonly class PostManager implements PostService
{
    public function __construct(private PostRepository $repository)
    {
    }

    /**
     * @inheritDoc
     */
    public function createPost(
        string $title,
        string $slug,
        array $category,
        PostBodyType $bodyType,
        string $body,
        User $author,
        array $tags = [],
    ): Post {
        $post = (new Post())
            ->setTitle($title)
            ->setSlug($slug)
            ->setBody($body)
            ->setBodyType($bodyType)
            ->setAuthor($author)
            ->setCategory($category)
            ->setCreatedAt(new DateTimeImmutable())
            ->setCreatedBy($author)
            ->setUpdatedAt(new DateTimeImmutable())
            ->setUpdatedBy($author);

        foreach ($tags as $tag) {
            $post->addTag($tag);
        }

        return $post;
    }

    public function getRepository(): PostRepository
    {
        return $this->repository;
    }
}
