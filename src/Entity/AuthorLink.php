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

use App\Doctrine\Traits\Timestampable;
use App\Repository\AuthorLinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: AuthorLinkRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AuthorLink
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface $id;

    #[ORM\Column(enumType: AuthorLinkType::class)]
    private AuthorLinkType $type;

    #[ORM\Column(type: 'url')]
    private UriInterface $url;

    #[ORM\ManyToOne(targetEntity: Author::class, cascade: ['persist'], inversedBy: 'links')]
    private ?Author $author = null;

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): static
    {
        $this->author = $author;

        if (!$author->getLinks()->contains($this)) {
            $this->author->addLink($this);
        }

        return $this;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getType(): AuthorLinkType
    {
        return $this->type;
    }

    public function setType(AuthorLinkType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUrl(): UriInterface
    {
        return $this->url;
    }

    public function setUrl(UriInterface $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function removeAuthor(): static
    {
        $this->author?->getLinks()->removeElement($this);
        $this->author = null;

        return $this;
    }
}
