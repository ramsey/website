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
use DateTimeInterface;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

use function preg_match;

final readonly class PostManager implements PostService
{
    private const string SLUG_PATTERN = '/^[a-zA-Z0-9\-]+$/';

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
        $this->checkId($post, $parsedPost->metadata->id);
        $this->checkSlug($post, $parsedPost->metadata->slug);
        $this->checkCreatedAt($post, $parsedPost->metadata->createdAt);

        $post
            ->setStatus($parsedPost->metadata->status)
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

    private function checkCreatedAt(Post $post, DateTimeInterface $createdAt): void
    {
        if ($post->getCreatedAt()->format('U') !== $createdAt->format('U')) {
            throw new InvalidArgumentException(
                'Unable to update post with parsed post having a different creation date',
            );
        }
    }

    private function checkId(Post $post, UuidInterface $id): void
    {
        if ($post->getId()->getBytes() !== $id->getBytes()) {
            throw new InvalidArgumentException('Unable to update post with parsed post having a different ID');
        }
    }

    private function checkSlug(Post $post, string $slug): void
    {
        if ($post->getSlug() !== $slug) {
            throw new InvalidArgumentException('Unable to update post with parsed post having a different slug');
        }

        if (!preg_match(self::SLUG_PATTERN, $slug)) {
            throw new InvalidArgumentException("Slug is invalid: $slug");
        }
    }
}
