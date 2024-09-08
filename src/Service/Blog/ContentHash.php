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

use App\Entity\Author;
use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostStatus;
use App\Entity\PostTag;
use DateTimeInterface;

use function array_map;
use function array_unique;
use function hash;
use function implode;
use function sort;
use function strtolower;
use function trim;

/**
 * Hashes post content in order to determine equality and whether the content
 * of a post has changed
 */
final readonly class ContentHash
{
    private string $authors;
    private string $categories;
    private string $content;
    private string $description;
    private string $keywords;
    private string $modifiedAt;
    private string $publishedAt;
    private string $slug;
    private string $status;
    private string $tags;
    private string $title;
    private string $type;

    /**
     * @param list<string> $authors
     * @param list<PostCategory> $categories
     * @param list<string> $keywords
     * @param list<string> $tags
     */
    private function __construct(
        array $authors,
        array $categories,
        string $content,
        string $description,
        array $keywords,
        ?DateTimeInterface $modifiedAt,
        ?DateTimeInterface $publishedAt,
        string $slug,
        PostStatus $status,
        array $tags,
        string $title,
        PostBodyType $type,
    ) {
        $authors = array_unique(array_map(fn (string $v): string => strtolower(trim($v)), $authors));
        sort($authors);

        $categories = array_unique(array_map(fn (PostCategory $v): string => strtolower(trim($v->value)), $categories));
        sort($categories);

        $keywords = array_unique(array_map(fn (string $v): string => strtolower(trim($v)), $keywords));
        sort($keywords);

        $tags = array_unique(array_map(fn (string $v): string => strtolower(trim($v)), $tags));
        sort($tags);

        $this->authors = implode(', ', $authors);
        $this->categories = implode(',', $categories);
        $this->content = trim($content);
        $this->description = trim($description);
        $this->keywords = implode(',', $keywords);
        $this->modifiedAt = $modifiedAt?->format('Y-m-d H:i:s') ?? '';
        $this->publishedAt = $publishedAt?->format('Y-m-d H:i:s') ?? '';
        $this->slug = trim($slug);
        $this->status = $status->value;
        $this->tags = implode(',', $tags);
        $this->title = trim($title);
        $this->type = $type->value;
    }

    public static function createFromParsedPost(ParsedPost $post): self
    {
        return new self(
            array_map(fn (ParsedPostAuthor $v): string => $v->byline . ' ' . $v->email, $post->authors),
            $post->metadata->categories,
            $post->content,
            (string) $post->metadata->description,
            $post->metadata->keywords,
            $post->metadata->modifiedAt,
            $post->metadata->publishedAt,
            $post->metadata->slug,
            $post->metadata->status,
            $post->metadata->tags,
            $post->metadata->title,
            $post->metadata->contentType,
        );
    }

    public static function createFromPost(Post $post): self
    {
        return new self(
            array_map(fn (Author $v): string => $v->getByline() . ' ' . $v->getEmail(), $post->getAuthors()->toArray()),
            $post->getCategory(),
            $post->getBody(),
            (string) $post->getDescription(),
            $post->getKeywords(),
            $post->getModifiedAt(),
            $post->getPublishedAt(),
            $post->getSlug(),
            $post->getStatus(),
            array_map(fn (PostTag $t): string => $t->getName(), $post->getTags()->toArray()),
            $post->getTitle(),
            $post->getBodyType(),
        );
    }

    public function equals(ContentHash $hash): bool
    {
        return $this->getHash() === $hash->getHash();
    }

    public function getHash(): string
    {
        return hash('sha256', $this->getContentForHash());
    }

    private function getContentForHash(): string
    {
        return $this->title . ';'
            . $this->type . ';'
            . $this->status . ';'
            . $this->authors . ';'
            . $this->publishedAt . ';'
            . $this->modifiedAt . ';'
            . $this->categories . ';'
            . $this->tags . ';'
            . $this->keywords . ';'
            . $this->slug . ';'
            . $this->description . ';'
            . $this->content;
    }
}
