<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Pages have titles, body content, and arbitrary metadata
 */
final class Page
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly Metadata $metadata = new Metadata([]),
    ) {
    }
}
