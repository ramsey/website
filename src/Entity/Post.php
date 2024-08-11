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

use App\Doctrine\Traits\SoftDeleteable;
use App\Doctrine\Traits\Timestampable;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Index(fields: ['createdAt', 'slug'])]
class Post
{
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $id;

    /**
     * @var Collection<int, Author>
     */
    #[ORM\JoinTable(name: 'posts_authors')]
    #[ORM\ManyToMany(targetEntity: Author::class, inversedBy: 'posts', cascade: ['persist'])]
    private Collection $authors;

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

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $keywords = [];

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
    #[ORM\ManyToMany(targetEntity: PostTag::class, inversedBy: 'posts', cascade: ['persist'])]
    private Collection $tags;

    /**
     * @var array<string, mixed[] | scalar | null> Additional metadata related to the post
     */
    #[ORM\Column(nullable: true, options: ['jsonb' => true])]
    private array $metadata = [];

    #[ORM\Column(length: 20, nullable: true, enumType: PostStatus::class)]
    private PostStatus $status = PostStatus::Draft;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->shortUrls = new ArrayCollection();
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }

        if (!$author->getPosts()->contains($this)) {
            $author->addPost($this);
        }

        return $this;
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

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
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

    public function setId(UuidInterface $id): static
    {
        if (isset($this->id) && $this->id->getBytes() !== $id->getBytes()) {
            throw new InvalidArgumentException('Cannot overwrite an existing ID with a different ID');
        }

        $this->id = $id;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @param list<string> $keywords
     */
    public function setKeywords(array $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * @return array<string, mixed[] | scalar | null>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed[] | scalar | null> $metadata
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

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

    public function getStatus(): PostStatus
    {
        return $this->status;
    }

    public function setStatus(PostStatus $status): static
    {
        $this->status = $status;

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

    public function removeAuthor(Author $author): static
    {
        $this->authors->removeElement($author);

        if ($author->getPosts()->contains($this)) {
            $author->removePost($this);
        }

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
