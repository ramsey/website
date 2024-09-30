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

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\Blog\ParsedPost;
use Psr\Http\Message\UriInterface;

/**
 * A service for interacting with posts
 *
 * @extends EntityService<int, Post>
 */
interface PostService extends EntityService
{
    /**
     * Returns a UriInterface for the given Post entity
     */
    public function buildUrl(Post $post): ?UriInterface;

    /**
     * Creates a Post entity from a ParsedPost
     */
    public function createFromParsedPost(ParsedPost $parsedPost): Post;

    public function getRepository(): PostRepository;

    /**
     * Updates a Post entity from a ParsedPost
     */
    public function updateFromParsedPost(Post $post, ParsedPost $parsedPost): Post;

    /**
     * Creates or updates a Post entity from a ParsedPost
     *
     * @throws EntityExists if `$doUpdate` is `false` and the entity already exists
     */
    public function upsertFromParsedPost(ParsedPost $parsedPost, bool $doUpdate = false): Post;
}
