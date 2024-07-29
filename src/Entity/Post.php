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
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Index(fields: ['createdAt', 'slug'])]
class Post
{
    use Blamable;
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?User $author = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $slug;

    /**
     * @var PostCategory[]
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: PostCategory::class)]
    private array $category = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $keywords = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $feedId = null;

    #[ORM\Column(enumType: PostBodyType::class)]
    private PostBodyType $bodyType;

    #[ORM\Column(type: Types::TEXT)]
    private string $body;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    /**
     * @var Collection<int, ShortUrl>
     */
    #[ORM\JoinTable(name: 'posts_short_urls')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'short_url_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: ShortUrl::class)]
    private Collection $shortUrls;

    /**
     * @var Collection<int, PostTag>
     */
    #[ORM\JoinTable(name: 'posts_post_tags')]
    #[ORM\ManyToMany(targetEntity: PostTag::class, inversedBy: 'posts')]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->shortUrls = new ArrayCollection();
    }

    public function addShortUrl(ShortUrl $shortUrl): static
    {
        if (!$this->shortUrls->contains($shortUrl)) {
            $this->shortUrls->add($shortUrl);
        }

        return $this;
    }

    public function addTag(PostTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getBodyType(): PostBodyType
    {
        return $this->bodyType;
    }

    public function setBodyType(PostBodyType $bodyType): static
    {
        $this->bodyType = $bodyType;

        return $this;
    }

    /**
     * @return PostCategory[]
     */
    public function getCategory(): array
    {
        return $this->category;
    }

    /**
     * @param PostCategory[] $category
     */
    public function setCategory(array $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getFeedId(): ?string
    {
        return $this->feedId;
    }

    public function setFeedId(?string $feedId): static
    {
        $this->feedId = $feedId;

        return $this;
    }

    /**
     * Returns the post's database identifier
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * @return Collection<int, ShortUrl>
     */
    public function getShortUrls(): Collection
    {
        return $this->shortUrls;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, PostTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function removeShortUrl(ShortUrl $shortUrl): static
    {
        $this->shortUrls->removeElement($shortUrl);

        return $this;
    }

    public function removeTag(PostTag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}