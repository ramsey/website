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
use DateTimeImmutable;
use InvalidArgumentException;
use PhpExtended\Email\MailboxList;
use PhpExtended\Email\MailboxListParserInterface;
use PhpExtended\Parser\ParseThrowable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\String\Slugger\SluggerInterface;
use Throwable;
use Webuni\FrontMatter\FrontMatterInterface;

use function is_array;
use function sprintf;
use function strtolower;
use function trim;

/**
 * @phpstan-import-type PostMetadata from ParsedPostMetadata
 */
final readonly class StaticFileParser implements PostParser
{
    public function __construct(
        private Filesystem $filesystem,
        private FrontMatterInterface $frontMatter,
        private MailboxListParserInterface $mailboxListParser,
        private SluggerInterface $slugger,
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
            $this->parseAuthors($metadata),
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

        return new ParsedPostMetadata(
            id: isset($metadata['id']) ? Uuid::fromString($metadata['id']) : Uuid::uuid7(),
            contentType: $type,
            title: $metadata['title'] ?? throw new InvalidArgumentException('Posts must have a title'),
            slug: $metadata['slug'] ?? strtolower((string) $this->slugger->slug($metadata['title'])),
            status: PostStatus::tryFrom($metadata['status'] ?? 'undefined')
                ?? throw new InvalidArgumentException('Posts must have a valid status'),
            categories: $categories,
            tags: $metadata['tags'] ?? [],
            description: $metadata['description'] ?? null,
            keywords: $metadata['keywords'] ?? [],
            excerpt: $metadata['excerpt'] ?? null,
            feedId: $metadata['feed_id'] ?? null,
            additional: $this->getAdditionalMetadata($metadata),
            createdAt: $this->parseDate($metadata, 'created'),
            publishedAt: $this->parseDate($metadata, 'published'),
            modifiedAt: $this->parseDate($metadata, 'modified'),
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
            $metadata['authors'],
            $metadata['title'],
            $metadata['slug'],
            $metadata['status'],
            $metadata['categories'],
            $metadata['tags'],
            $metadata['description'],
            $metadata['keywords'],
            $metadata['excerpt'],
            $metadata['feed_id'],
            $metadata['created'],
            $metadata['published'],
            $metadata['modified'],
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

    /**
     * @param PostMetadata $metadata
     *
     * @return list<ParsedPostAuthor>
     */
    private function parseAuthors(array $metadata): array
    {
        if (!isset($metadata['authors'])) {
            return [];
        }

        $authorsData = $metadata['authors'];
        if (!is_array($authorsData)) {
            $authorsData = [$authorsData];
        }

        $authors = [];
        foreach ($authorsData as $authorData) {
            try {
                /** @var MailboxList $mailbox */
                $mailbox = $this->mailboxListParser->parse($authorData);

                foreach ($mailbox as $address) {
                    $authors[] = new ParsedPostAuthor(
                        trim($address->getDisplayName()),
                        $address->getEmailAddress()->getCanonicalRepresentation(),
                    );
                }
            } catch (ParseThrowable) {
                throw new InvalidArgumentException('When provided, authors must have valid mailbox strings');
            }
        }

        return $authors;
    }

    /**
     * @param PostMetadata $metadata
     * @param 'created' | 'published' | 'modified' $property
     */
    private function parseDate(array $metadata, string $property): ?DateTimeImmutable
    {
        try {
            return isset($metadata[$property])
                ? new DateTimeImmutable('@' . $metadata[$property])
                : null;
        } catch (Throwable) {
            throw new InvalidArgumentException("When provided, $property must be a valid date");
        }
    }
}
