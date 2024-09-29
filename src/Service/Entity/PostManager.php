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
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Requirement\Requirement;

use function preg_match;
use function sprintf;

final readonly class PostManager implements PostService
{
    public function __construct(
        private PostRepository $repository,
        private PostTagService $postTagService,
        private ShortUrlService $shortUrlService,
        private AuthorService $authorService,
    ) {
    }

    public function createFromParsedPost(ParsedPost $parsedPost): Post
    {
        $post = (new Post())
            ->setId($parsedPost->metadata->id)
            ->setSlug($parsedPost->metadata->slug);

        if ($parsedPost->metadata->createdAt !== null) {
            $post->setCreatedAt($parsedPost->metadata->createdAt);
        }

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

        if ($parsedPost->metadata->publishedAt !== null) {
            $post->setPublishedAt($parsedPost->metadata->publishedAt);
        }

        if ($parsedPost->metadata->modifiedAt !== null) {
            $post->setModifiedAt($parsedPost->metadata->modifiedAt);
        }

        // Remove any tags and then add them back from the metadata.
        $post->getTags()->clear();
        foreach ($parsedPost->metadata->tags as $tagName) {
            $tag = $this->postTagService->getRepository()->findOneByName($tagName)
                ?? $this->postTagService->createTag($tagName);
            $post->addTag($tag);
        }

        // Remove any authors and add them back.
        $post->getAuthors()->clear();
        foreach ($parsedPost->authors as $authorData) {
            $author = $this->authorService->getRepository()->findOneBy(['email' => $authorData->email])
                ?? $this->authorService->createAuthor($authorData->byline, $authorData->email);
            $post->addAuthor($author);
        }

        /** @var string | null $shortUrl */
        $shortUrl = $parsedPost->metadata->additional['shorturl'] ?? null;
        $this->associateShortUrl($post, $shortUrl);

        return $post;
    }

    public function upsertFromParsedPost(ParsedPost $parsedPost, bool $doUpdate = false): Post
    {
        $existingPost = $this->getRepository()->find($parsedPost->metadata->id);

        if ($existingPost !== null) {
            if (!$doUpdate) {
                throw new EntityExists(sprintf(
                    "A post with ID '%s' already exists; call %s with TRUE as the second parameter to update the post",
                    $parsedPost->metadata->id,
                    __METHOD__,
                ));
            }

            return $this->updateFromParsedPost($existingPost, $parsedPost);
        }

        return $this->createFromParsedPost($parsedPost);
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

        if (!preg_match('/^' . Requirement::ASCII_SLUG . '$/', $slug)) {
            throw new InvalidArgumentException("Slug is invalid: $slug");
        }
    }
}
