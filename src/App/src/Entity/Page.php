<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Website pages have titles, body content, and arbitrary attributes
 */
class Page
{
    public function __construct(
        private string $title,
        private string $content,
        private Attributes $attributes,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAttributes(): Attributes
    {
        return $this->attributes;
    }
}
