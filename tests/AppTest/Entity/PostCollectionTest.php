<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Post;
use App\Entity\PostCollection;
use Ramsey\Collection\Exception\InvalidArgumentException;
use Ramsey\Test\Website\TestCase;

class PostCollectionTest extends TestCase
{
    public function testPostCollectionAcceptsPostEntities(): void
    {
        $collection = new PostCollection();

        $collection[] = new Post($this->faker()->sentence, $this->faker()->paragraph, $this->faker()->dateTime);
        $collection[] = new Post($this->faker()->sentence, $this->faker()->paragraph, $this->faker()->dateTime);
        $collection[] = new Post($this->faker()->sentence, $this->faker()->paragraph, $this->faker()->dateTime);
        $collection[] = new Post($this->faker()->sentence, $this->faker()->paragraph, $this->faker()->dateTime);
        $collection[] = new Post($this->faker()->sentence, $this->faker()->paragraph, $this->faker()->dateTime);

        $this->assertCount(5, $collection);
    }

    public function testPostCollectionDeniesOtherTypes(): void
    {
        $collection = new PostCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be of type App\Entity\Post; value is foo');

        $collection[] = 'foo'; // @phpstan-ignore-line
    }
}
