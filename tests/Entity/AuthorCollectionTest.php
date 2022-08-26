<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\AuthorCollection;
use App\Tests\TestCase;

class AuthorCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new AuthorCollection([]);

        $this->assertSame(Author::class, $collection->getType());
    }

    public function testCollection(): void
    {
        $collection = new AuthorCollection([new Author('Frodo')]);
        $collection[] = new Author('Samwise');

        $this->assertCount(2, $collection);
    }
}
