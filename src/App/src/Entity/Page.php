<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Website pages have titles, body content, and arbitrary attributes
 */
class Page
{
    private Attributes $attributes;

    public function __construct(
        private string $title,
        private string $content,
        ?Attributes $attributes = null,
    ) {
        $this->attributes = $attributes ?? new Attributes([]);
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
