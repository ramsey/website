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
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Author
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    private string $byline;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'authors', cascade: ['persist'])]
    private Collection $posts;

    /**
     * @var Collection<int, AuthorLink>
     */
    #[ORM\OneToMany(
        targetEntity: AuthorLink::class,
        mappedBy: 'author',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $links;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }

        if (!$post->getAuthors()->contains($this)) {
            $post->addAuthor($this);
        }

        return $this;
    }

    public function addLink(AuthorLink $link): static
    {
        if ($link->getAuthor() !== null && $link->getAuthor() !== $this) {
            throw new InvalidArgumentException('An author is already associated with this link');
        }

        if (!$this->links->contains($link)) {
            $this->links->add($link);
        }

        if ($link->getAuthor() === null) {
            $link->setAuthor($this);
        }

        return $this;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getByline(): string
    {
        return $this->byline;
    }

    public function setByline(string $byline): static
    {
        $this->byline = $byline;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, AuthorLink>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function removeLink(AuthorLink $link): static
    {
        if ($link->getAuthor() === $this) {
            $link->removeAuthor();
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($post->getAuthors()->contains($this)) {
            $post->removeAuthor($this);
        }

        return $this;
    }
}
