<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;

/**
 * Website posts are pages posted at specific times with authors
 */
class Post extends Page
{
    public function __construct(
        private string $title,
        private string $content,
        private DateTimeInterface $publishDate,
        private ?AuthorCollection $authors = null,
        private ?Attributes $attributes = null,
        private ?DateTimeInterface $lastUpdateDate = null,
    ) {
        parent::__construct($this->title, $this->content, $this->attributes);
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
