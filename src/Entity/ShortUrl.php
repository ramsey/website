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

namespace App\Entity;

use App\Doctrine\Traits\Blamable;
use App\Doctrine\Traits\SoftDeleteable;
use App\Doctrine\Traits\Timestampable;
use App\Repository\ShortUrlRepository;
use Doctrine\ORM\Mapping as ORM;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

/**
 * Represents a short URL with a slug or custom slug that redirect to a specified destination URL
 */
#[ORM\Entity(repositoryClass: ShortUrlRepository::class)]
#[ORM\Index(fields: ['destinationUrl'])]
class ShortUrl
{
    use Blamable;
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface $id;

    #[ORM\Column(length: 50, unique: true)]
    private string $slug;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    private ?string $customSlug = null;

    #[ORM\Column(type: 'url')]
    private ?UriInterface $destinationUrl = null;

    /**
     * Returns the short URL's database identifier
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns the auto-generated slug for the short URL
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Sets the auto-generated slug for the short URL
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Returns a custom slug (if set) for the short URL
     */
    public function getCustomSlug(): ?string
    {
        return $this->customSlug;
    }

    /**
     * Sets a custom slug for the short URL
     */
    public function setCustomSlug(string $customSlug): static
    {
        $this->customSlug = $customSlug;

        return $this;
    }

    /**
     * Returns the destination (or redirect) URL for the short URL
     */
    public function getDestinationUrl(): ?UriInterface
    {
        return $this->destinationUrl;
    }

    /**
     * Sets a destination (or redirect) URL for the short URL
     */
    public function setDestinationUrl(UriInterface $destinationUrl): static
    {
        $this->destinationUrl = $destinationUrl;

        return $this;
    }
}
