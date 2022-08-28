<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\AuthorCollection;
use App\Entity\BlogPost;
use App\Entity\Metadata;
use App\Tests\TestCase;
use DateTimeImmutable;

class BlogPostTest extends TestCase
{
    public function testDefaultProperties(): void
    {
        $date = new DateTimeImmutable();

        $post = new BlogPost(
            title: 'A Blog Post',
            content: 'Content of a blog post.',
            published: $date,
            slug: 'blog-slug',
        );

        $this->assertSame('A Blog Post', $post->title);
        $this->assertSame('Content of a blog post.', $post->content);
        $this->assertSame($date, $post->published);
        $this->assertInstanceOf(AuthorCollection::class, $post->authors);
        $this->assertCount(0, $post->authors);
        $this->assertInstanceOf(Metadata::class, $post->metadata);
        $this->assertCount(0, $post->metadata);
        $this->assertNull($post->lastUpdated);
    }

    public function testAuthors(): void
    {
        $authors = new AuthorCollection([
            new Author('Melian'),
        ]);

        $post = new BlogPost(
            'A Blog Post',
            'Content of a blog post.',
            new DateTimeImmutable(),
            'blog-slug',
            authors: $authors,
        );

        $this->assertSame($authors, $post->authors);
    }

    public function testMetadata(): void
    {
        $metadata = new Metadata([
            'foo' => 'bar',
        ]);

        $post = new BlogPost(
            'A Blog Post',
            'Content of a blog post.',
            new DateTimeImmutable(),
            'blog-slug',
            metadata: $metadata,
        );

        $this->assertSame($metadata, $post->metadata);
    }

    public function testLastUpdated(): void
    {
        $lastUpdated = new DateTimeImmutable();

        $post = new BlogPost(
            'A Blog Post',
            'Content of a blog post.',
            new DateTimeImmutable(),
            'blog-slug',
            lastUpdated: $lastUpdated,
        );

        $this->assertSame($lastUpdated, $post->lastUpdated);
    }
}
