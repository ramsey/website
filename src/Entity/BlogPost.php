<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;

/**
 * Blog posts are pages posted at specific times with authors
 */
final class BlogPost
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly DateTimeInterface $published,
        public readonly AuthorCollection $authors = new AuthorCollection([]),
        public readonly Metadata $metadata = new Metadata([]),
        public readonly ?DateTimeInterface $lastUpdated = null,
    ) {
    }
}
