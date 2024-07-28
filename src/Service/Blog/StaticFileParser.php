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
use DateTimeImmutable;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Throwable;
use Webuni\FrontMatter\FrontMatterInterface;

use function sprintf;

/**
 * @phpstan-import-type PostMetadata from ParsedPostMetadata
 */
final readonly class StaticFileParser implements PostParser
{
    public function __construct(
        public Filesystem $filesystem,
        public FrontMatterInterface $frontMatter,
    ) {
    }

    public function parse(string $content): ParsedPost
    {
        if (!$this->filesystem->exists($content)) {
            throw new InvalidArgumentException(sprintf('Could not find file %s', $content));
        }

        $document = $this->frontMatter->parse($this->filesystem->readFile($content));

        /** @var PostMetadata $metadata */
        $metadata = $document->getData();

        return new ParsedPost(
            $this->parseMetadata($metadata, $this->getContentType($content)),
            $document->getContent(),
        );
    }

    /**
     * @param PostMetadata $metadata
     */
    private function parseMetadata(array $metadata, PostBodyType $type): ParsedPostMetadata
    {
        $categories = [];
        foreach ($metadata['categories'] ?? [] as $category) {
            $categories[] = PostCategory::from($category);
        }

        try {
            $createdAt = isset($metadata['date'])
                ? new DateTimeImmutable("@{$metadata['date']}")
                : throw new RuntimeException('Missing date');
        } catch (Throwable) {
            throw new InvalidArgumentException('Posts must have a valid date');
        }

        try {
            $updatedAt = isset($metadata['updated'])
                ? new DateTimeImmutable("@{$metadata['updated']}")
                : null;
        } catch (Throwable) {
            throw new InvalidArgumentException('When provided, updated must be a valid date');
        }

        return new ParsedPostMetadata(
            id: isset($metadata['id']) ? Uuid::fromString($metadata['id']) : Uuid::uuid7(),
            contentType: $type,
            title: $metadata['title'] ?? throw new InvalidArgumentException('Posts must have a title'),
            slug: $metadata['slug'] ?? throw new InvalidArgumentException('Posts must have a slug'),
            categories: $categories,
            tags: $metadata['tags'] ?? [],
            description: $metadata['description'] ?? null,
            keywords: $metadata['keywords'] ?? [],
            excerpt: $metadata['excerpt'] ?? null,
            feedId: $metadata['feed_id'] ?? null,
            additional: $this->getAdditionalMetadata($metadata),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * Returns an array of metadata excluding properties that are set on ParsedPostMetadata
     *
     * @param PostMetadata $metadata
     *
     * @return array<string, mixed[] | scalar | null>
     */
    private function getAdditionalMetadata(array $metadata): array
    {
        unset(
            $metadata['id'],
            $metadata['title'],
            $metadata['slug'],
            $metadata['categories'],
            $metadata['tags'],
            $metadata['description'],
            $metadata['keywords'],
            $metadata['excerpt'],
            $metadata['feed_id'],
            $metadata['date'],
            $metadata['updated'],
        );

        return $metadata;
    }

    private function getContentType(string $path): PostBodyType
    {
        return match (Path::getExtension($path, true)) {
            'html' => PostBodyType::Html,
            'markdown', 'md' => PostBodyType::Markdown,
            'rst' => PostBodyType::ReStructuredText,
            default => throw new InvalidArgumentException(
                sprintf('File does not have an acceptable extension: %s', $path),
            ),
        };
    }
}
