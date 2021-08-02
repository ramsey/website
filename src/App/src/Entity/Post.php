<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;

/**
 * Website posts are pages posted at specific times with authors
 */
class Post extends Page implements Attributable
{
    public function __construct(
        string $title,
        string $content,
        private DateTimeInterface $publishDate,
        private ?AuthorCollection $authors = null,
        ?Attributes $attributes = null,
        private ?DateTimeInterface $lastUpdateDate = null,
    ) {
        parent::__construct($title, $content, $attributes);
    }

    public function getPublishDate(): DateTimeInterface
    {
        return $this->publishDate;
    }

    public function getAuthors(): ?AuthorCollection
    {
        return $this->authors;
    }

    public function getLastUpdateDate(): ?DateTimeInterface
    {
        return $this->lastUpdateDate;
    }
}
