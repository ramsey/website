<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Author;
use App\Entity\AuthorCollection;
use Ramsey\Collection\Exception\InvalidArgumentException;
use Ramsey\Test\Website\TestCase;

class AuthorCollectionTest extends TestCase
{
    public function testAuthorCollectionAcceptsAuthorEntities(): void
    {
        $collection = new AuthorCollection();

        $collection[] = new Author($this->faker()->name);
        $collection[] = new Author($this->faker()->name);
        $collection[] = new Author($this->faker()->name);

        $this->assertCount(3, $collection);
    }

    public function testAuthorCollectionDeniesOtherTypes(): void
    {
        $collection = new AuthorCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be of type App\Entity\Author; value is foo');

        $collection[] = 'foo'; // @phpstan-ignore-line
    }
}
