<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Attributes;
use App\Entity\Author;
use Laminas\Diactoros\Uri;
use Ramsey\Test\Website\TestCase;

class AuthorTest extends TestCase
{
    public function testGetName(): void
    {
        $name = $this->faker()->name;
        $author = new Author(name: $name);

        $this->assertSame($name, $author->getName());
    }

    public function testGetBiography(): void
    {
        $biography = $this->faker()->paragraph;
        $author = new Author(name: 'Jane Doe', biography: $biography);

        $this->assertSame($biography, $author->getBiography());
    }

    public function testGetBiographyReturnsNullByDefault(): void
    {
        $author = new Author(name: 'Jane Doe');

        $this->assertNull($author->getBiography());
    }

    public function testGetUrl(): void
    {
        $url = new Uri($this->faker()->url);
        $author = new Author(name: 'Jane Doe', url: $url);

        $this->assertSame($url, $author->getUrl());
    }

    public function testGetUrlReturnsNullByDefault(): void
    {
        $author = new Author(name: 'Jane Doe');

        $this->assertNull($author->getUrl());
    }

    public function testGetImageUrl(): void
    {
        $imageUrl = new Uri($this->faker()->imageUrl);
        $author = new Author(name: 'Jane Doe', imageUrl: $imageUrl);

        $this->assertSame($imageUrl, $author->getImageUrl());
    }

    public function testGetImageUrlReturnsNullByDefault(): void
    {
        $author = new Author(name: 'Jane Doe');

        $this->assertNull($author->getImageUrl());
    }

    public function testGetEmail(): void
    {
        $email = $this->faker()->safeEmail;
        $author = new Author(name: 'Jane Doe', email: $email);

        $this->assertSame($email, $author->getEmail());
    }

    public function testGetEmailReturnsNullByDefault(): void
    {
        $author = new Author(name: 'Jane Doe');

        $this->assertNull($author->getEmail());
    }

    public function testGetAttributesReturnsAttributesEvenWhenNoneArePassed(): void
    {
        $author = new Author(name: 'Jane Doe');

        $this->assertInstanceOf(Attributes::class, $author->getAttributes());
    }

    public function testGetAttributesReturnsPassedAttributes(): void
    {
        $attributes = new Attributes(['foo' => 'bar']);
        $author = new Author(name: 'Jane Doe', attributes: $attributes);

        $this->assertSame($attributes, $author->getAttributes());
    }
}
