<?php

declare(strict_types=1);

namespace App\Entity;

use Psr\Http\Message\UriInterface;

/**
 * An author of content on the website
 */
final class Author
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $biography = null,
        public readonly ?UriInterface $url = null,
        public readonly ?UriInterface $imageUrl = null,
        public readonly ?string $email = null,
        public readonly Metadata $metadata = new Metadata([]),
    ) {
    }
}
