<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Attributes;
use App\Entity\AuthorCollection;
use App\Entity\Post;
use DateTimeImmutable;
use DateTimeInterface;
use Ramsey\Test\Website\TestCase;

class PostTest extends TestCase
{
    private string $title;
    private string $content;
    private DateTimeInterface $publishDate;

    protected function setUp(): void
    {
        $this->title = $this->faker()->sentence;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);
        $this->content = $content;

        $this->publishDate = new DateTimeImmutable('28 February 2021');
    }

    public function testGetTitle(): void
    {
        $post = new Post($this->title, $this->content, $this->publishDate);

        $this->assertSame($this->title, $post->getTitle());
    }

    public function testGetContent(): void
    {
        $post = new Post($this->title, $this->content, $this->publishDate);

        $this->assertSame($this->content, $post->getContent());
    }

    public function testGetAttributes(): void
    {
        $attributes = new Attributes([]);

        $post = new Post($this->title, $this->content, $this->publishDate, attributes: $attributes);

        $this->assertSame($attributes, $post->getAttributes());
    }

    public function testGetAttributesDefaultsToEmptyAttributes(): void
    {
        $post = new Post($this->title, $this->content, $this->publishDate);
        $attributes = $post->getAttributes();

        $this->assertInstanceOf(Attributes::class, $attributes);

        // Ensure it returns the same instance created when calling it the first time.
        $this->assertSame($attributes, $post->getAttributes());
    }

    public function testGetPublishDate(): void
    {
        $post = new Post($this->title, $this->content, $this->publishDate);

        $this->assertSame($this->publishDate, $post->getPublishDate());
    }

    public function testGetAuthorsIsEmptyWhenNotProvided(): void
    {
        $post = new Post($this->title, $this->content, $this->publishDate);

        $this->assertEmpty($post->getAuthors());
    }

    public function testGetAuthorsReturnsProvidedAuthorCollection(): void
    {
        $authorCollection = new AuthorCollection();

        $post = new Post($this->title, $this->content, $this->publishDate, authors: $authorCollection);

        $this->assertSame($authorCollection, $post->getAuthors());
    }

    public function testGetLastUpdateDateIsNullWhenNotProvided(): void
    {
        $post = new Post($this->title, $this->content, $this->publishDate);

        $this->assertNull($post->getLastUpdateDate());
    }

    public function testGetLastUpdateDateReturnsProvidedDateTime(): void
    {
        $date = new DateTimeImmutable('12 July 2021');

        $post = new Post($this->title, $this->content, $this->publishDate, lastUpdateDate: $date);

        $this->assertSame($date, $post->getLastUpdateDate());
    }
}
