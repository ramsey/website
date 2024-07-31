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
use App\Repository\PostRepository;
use App\Service\Blog\ParsedPost;
use DateTimeImmutable;
use InvalidArgumentException;

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
        array $tags = [],
    ): Post {
        $post = (new Post())
            ->setTitle($title)
            ->setSlug($slug)
            ->setBody($body)
            ->setBodyType($bodyType)
            ->setCategory($category)
            ->setCreatedAt(new DateTimeImmutable());

        foreach ($tags as $tag) {
            $post->addTag($tag);
        }

        return $post;
    }

    public function createFromParsedPost(ParsedPost $parsedPost): Post
    {
        $post = (new Post())
            ->setId($parsedPost->metadata->id)
            ->setSlug($parsedPost->metadata->slug)
            ->setCreatedAt($parsedPost->metadata->createdAt);

        return $this->updateFromParsedPost($post, $parsedPost);
    }

    public function getRepository(): PostRepository
    {
        return $this->repository;
    }

    /**
     * Updates a Post entity from a ParsedPost
     */
    public function updateFromParsedPost(Post $post, ParsedPost $parsedPost): Post
    {
        if ($post->getId()->getBytes() !== $parsedPost->metadata->id->getBytes()) {
            throw new InvalidArgumentException('Unable to update post with parsed post having a different ID');
        }

        if ($post->getSlug() !== $parsedPost->metadata->slug) {
            throw new InvalidArgumentException('Unable to update post with parsed post having a different slug');
        }

        if ($post->getCreatedAt()->format('U') !== $parsedPost->metadata->createdAt->format('U')) {
            throw new InvalidArgumentException(
                'Unable to update post with parsed post having a different creation date',
            );
        }

        $post
            ->setTitle($parsedPost->metadata->title)
            ->setBody($parsedPost->content)
            ->setBodyType($parsedPost->metadata->contentType)
            ->setDescription($parsedPost->metadata->description)
            ->setKeywords($parsedPost->metadata->keywords)
            ->setExcerpt($parsedPost->metadata->excerpt)
            ->setFeedId($parsedPost->metadata->feedId)
            ->setMetadata($parsedPost->metadata->additional)
            ->setCategory($parsedPost->metadata->categories);

        if ($parsedPost->metadata->updatedAt !== null) {
            $post->setUpdatedAt($parsedPost->metadata->updatedAt);
        }

        // Remove any tags and then add them back from the metadata.
        $post->getTags()->clear();
        foreach ($parsedPost->metadata->tags as $tagName) {
            $tag = $this->postTagService->getRepository()->findOneByName($tagName)
                ?? $this->postTagService->createTag($tagName);
            $post->addTag($tag);
        }

        /** @var string | null $shortUrl */
        $shortUrl = $parsedPost->metadata->additional['shorturl'] ?? null;
        $this->associateShortUrl($post, $shortUrl);

        return $post;
    }

    private function associateShortUrl(Post $post, ?string $url): void
    {
        if ($url === null) {
            return;
        }

        $shortUrl = $this->shortUrlService->getRepository()->getShortUrlForShortUrl($url);

        if ($shortUrl !== null) {
            $post->addShortUrl($shortUrl);
        }
    }
}
