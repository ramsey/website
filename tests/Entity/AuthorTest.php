<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\Metadata;
use App\Tests\TestCase;
use Nyholm\Psr7\Uri;

class AuthorTest extends TestCase
{
    public function testDefaultProperties(): void
    {
        $author = new Author(name: 'Frodo Baggins');

        $this->assertSame('Frodo Baggins', $author->name);
        $this->assertNull($author->biography);
        $this->assertNull($author->url);
        $this->assertNull($author->imageUrl);
        $this->assertNull($author->email);
        $this->assertInstanceOf(Metadata::class, $author->metadata);
        $this->assertCount(0, $author->metadata);
    }

    public function testBiography(): void
    {
        $author = new Author('Arwen', biography: 'A biography');

        $this->assertSame('A biography', $author->biography);
    }

    public function testUrl(): void
    {
        $url = new Uri('https://example.com');
        $author = new Author('Éowyn', url: $url);

        $this->assertSame($url, $author->url);
    }

    public function testImageUrl(): void
    {
        $url = new Uri('https://example.com/image');
        $author = new Author('Lúthien', imageUrl: $url);

        $this->assertSame($url, $author->imageUrl);
    }

    public function testEmail(): void
    {
        $author = new Author('Eärwen', email: 'earwen@example.com');

        $this->assertSame('earwen@example.com', $author->email);
    }

    public function testMetadata(): void
    {
        $metadata = new Metadata([
            'foo' => 'bar',
        ]);
        $author = new Author('Galadriel', metadata: $metadata);

        $this->assertSame($metadata, $author->metadata);
    }
}
