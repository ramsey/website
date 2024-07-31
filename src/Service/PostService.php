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
use App\Entity\PostCategory;
use App\Entity\PostTag;
use App\Service\Blog\ParsedPost;

/**
 * A service for interacting with posts
 *
 * @extends Service<int, Post>
 */
interface PostService extends Service
{
    /**
     * Creates a Post entity
     *
     * @param PostCategory[] $category
     * @param PostTag[] $tags
     */
    public function createPost(
        string $title,
        string $slug,
        array $category,
        PostBodyType $bodyType,
        string $body,
        array $tags = [],
    ): Post;

    /**
     * Creates a Post entity from a ParsedPost
     */
    public function createFromParsedPost(ParsedPost $parsedPost): Post;

    /**
     * Updates a Post entity from a ParsedPost
     */
    public function updateFromParsedPost(Post $post, ParsedPost $parsedPost): Post;
}
