<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\AuthorLink;
use App\Entity\AuthorLinkType;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Faker\Factory;
use Faker\Generator;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function count;

#[TestDox('AuthorLink entity')]
class AuthorLinkTest extends KernelTestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    #[Group('db')]
    #[TestDox('is fully populated when retrieved from the database')]
    public function testAuthorLinkEntityFromDatabase(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(AuthorLink::class);

        $links = $repository->findAll();

        $this->assertGreaterThan(0, count($links));
        $this->assertContainsOnlyInstancesOf(AuthorLink::class, $links);

        $link = $links[0];

        $this->assertInstanceOf(Author::class, $link->getAuthor());
        $this->assertInstanceOf(UuidInterface::class, $link->getId());
        $this->assertInstanceOf(AuthorLinkType::class, $link->getType());
        $this->assertInstanceOf(UriInterface::class, $link->getUrl());
        $this->assertInstanceOf(DateTimeImmutable::class, $link->getCreatedAt());
        $this->assertNull($link->getUpdatedAt());
    }

    #[TestDox('sets the author property')]
    public function testSetAuthor(): void
    {
        $author = new Author();
        $link = new AuthorLink();

        $this->assertSame($link, $link->setAuthor($author));
        $this->assertSame($author, $link->getAuthor());
        $this->assertTrue($author->getLinks()->contains($link));
    }

    #[TestDox('has no author set (i.e., null) on new instances')]
    public function testAuthorIsNullOnNewEntity(): void
    {
        $link = new AuthorLink();

        $this->assertNull($link->getAuthor());
    }

    #[TestDox('sets the type property')]
    public function testSetType(): void
    {
        $link = new AuthorLink();

        $this->assertSame($link, $link->setType(AuthorLinkType::Website));
        $this->assertSame(AuthorLinkType::Website, $link->getType());
    }

    public function testSetUrl(): void
    {
        $url = new Uri($this->faker->url());
        $link = new AuthorLink();

        $this->assertSame($link, $link->setUrl($url));
        $this->assertSame($url, $link->getUrl());
    }

    public function testRemoveAuthor(): void
    {
        $author = new Author();
        $link = new AuthorLink();

        $link->setAuthor($author);
        $link->removeAuthor();

        $this->assertNull($link->getAuthor());
        $this->assertFalse($author->getLinks()->contains($link));
    }
}
