<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\BlogPost;
use App\Entity\BlogPostCollection;
use App\Tests\TestCase;
use DateTimeImmutable;

class BlogPostCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new BlogPostCollection([]);

        $this->assertSame(BlogPost::class, $collection->getType());
    }

    public function testCollection(): void
    {
        $collection = new BlogPostCollection([new BlogPost('Title', 'Content', new DateTimeImmutable(), 'blog-slug')]);
        $collection[] = new BlogPost('Title', 'Content', new DateTimeImmutable(), 'blog-slug');

        $this->assertCount(2, $collection);
    }
}
