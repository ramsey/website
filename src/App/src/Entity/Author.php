<?php

declare(strict_types=1);

namespace App\Entity;

use Psr\Http\Message\UriInterface;

/**
 * An author of content on the website
 */
class Author
{
    public function __construct(
        private string $name,
        private ?string $biography = null,
        private ?UriInterface $url = null,
        private ?UriInterface $imageUrl = null,
        private ?string $email = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function getUrl(): ?UriInterface
    {
        return $this->url;
    }

    public function getImageUrl(): ?UriInterface
    {
        return $this->imageUrl;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
