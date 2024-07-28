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
use App\Entity\ShortUrl;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\Blog\ParsedPost;
use DateTimeImmutable;

final readonly class PostManager implements PostService
{
    public function __construct(
        private PostRepository $repository,
        private PostTagService $postTagService,
        private ShortUrlService $shortUrlService,
    ) {
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
        $createdAt = new DateTimeImmutable();

        $post = (new Post())
            ->setTitle($title)
            ->setSlug($slug)
            ->setBody($body)
            ->setBodyType($bodyType)
            ->setAuthor($author)
            ->setCategory($category)
            ->setCreatedAt($createdAt)
            ->setCreatedBy($author)
            ->setUpdatedAt($createdAt)
            ->setUpdatedBy($author);

        foreach ($tags as $tag) {
            $post->addTag($tag);
        }

        return $post;
    }

    public function createFromParsedPost(ParsedPost $parsedPost, User $author): Post
    {
        /** @var array{shorturl?: string} $additional */
        $additional = $parsedPost->metadata->additional;

        $post = (new Post())
            ->setId($parsedPost->metadata->id)
            ->setTitle($parsedPost->metadata->title)
            ->setSlug($parsedPost->metadata->slug)
            ->setBody($parsedPost->content)
            ->setBodyType($parsedPost->metadata->contentType)
            ->setDescription($parsedPost->metadata->description)
            ->setKeywords($parsedPost->metadata->keywords)
            ->setExcerpt($parsedPost->metadata->excerpt)
            ->setFeedId($parsedPost->metadata->feedId)
            ->setMetadata($additional)
            ->setAuthor($author)
            ->setCategory($parsedPost->metadata->categories)
            ->setCreatedAt($parsedPost->metadata->createdAt)
            ->setCreatedBy($author)
            ->setUpdatedAt($parsedPost->metadata->updatedAt ?? $parsedPost->metadata->createdAt)
            ->setUpdatedBy($author);

        foreach ($parsedPost->metadata->tags as $tagName) {
            $tag = $this->postTagService->getRepository()->findOneByName($tagName)
                ?? $this->postTagService->createTag($tagName);
            $post->addTag($tag);
        }

        $shortUrl = $this->getShortUrl($additional['shorturl'] ?? null);
        if ($shortUrl !== null) {
            $post->addShortUrl($shortUrl);
        }

        return $post;
    }

    public function getRepository(): PostRepository
    {
        return $this->repository;
    }

    private function getShortUrl(?string $url): ?ShortUrl
    {
        if ($url === null) {
            return null;
        }

        return $this->shortUrlService->getRepository()->getShortUrlForShortUrl($url);
    }
}
