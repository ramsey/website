<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\ChangedWebsiteUri;
use App\Repository\ChangedWebsiteUriRepository;
use Laminas\Diactoros\UriFactory;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class ChangedWebsiteUriRepositoryTest extends TestCase
{
    private ChangedWebsiteUriRepository $repository;

    public function setUp(): void
    {
        $this->repository = new ChangedWebsiteUriRepository(
            __DIR__ . '/fixtures/changed_website_uri.php',
            new UriFactory(),
        );
    }

    #[TestDox('getClassName() returns App\Entity\ChangedWebsiteUri')]
    public function testGetClassName(): void
    {
        $this->assertEquals(ChangedWebsiteUri::class, $this->repository->getClassName());
    }

    #[TestDox('find() returns null for an integer (non-string value)')]
    public function testFindReturnsNullForInteger(): void
    {
        $this->assertNull($this->repository->find(123));
    }

    #[TestDox('find() returns null when ID not found')]
    public function testFindReturnsNullWhenIdNotFound(): void
    {
        $this->assertNull($this->repository->find('not found'));
    }

    #[TestDox('find() returns an entity with a redirect URI')]
    public function testFindReturnsChangedWebsiteUriWithRedirectUri(): void
    {
        $entity = $this->repository->find('/foo/bar');

        $this->assertInstanceOf(ChangedWebsiteUri::class, $entity);
        $this->assertSame('/foo/bar', $entity->uri->getPath());
        $this->assertSame(307, $entity->httpStatusCode);
        $this->assertSame('https://other.example.com/foo/bar', (string) $entity->redirectUri);
    }

    #[TestDox('find() returns an equivalent entity ending with / or /index.html')]
    public function testFindReturnsChangedWebsiteUriEndingInSlash(): void
    {
        $entity1 = $this->repository->find('/foo/bar');
        $entity2 = $this->repository->find('/foo/bar/');
        $entity3 = $this->repository->find('/foo/bar/index.html');

        $this->assertInstanceOf(ChangedWebsiteUri::class, $entity1);
        $this->assertInstanceOf(ChangedWebsiteUri::class, $entity2);
        $this->assertInstanceOf(ChangedWebsiteUri::class, $entity3);

        // Using "equals" instead of "same" because these objects
        // do not have the same identity.
        $this->assertEquals($entity1, $entity2);
        $this->assertEquals($entity2, $entity3);

        $this->assertSame('/foo/bar', $entity1->uri->getPath());
        $this->assertSame('/foo/bar', $entity2->uri->getPath());
        $this->assertSame('/foo/bar', $entity3->uri->getPath());
    }

    #[TestDox('find() returns an entity without a redirect URI')]
    public function testFindReturnsChangedWebsiteUriWithoutRedirectUri(): void
    {
        $entity = $this->repository->find('/search');

        $this->assertInstanceOf(ChangedWebsiteUri::class, $entity);
        $this->assertSame('/search', $entity->uri->getPath());
        $this->assertSame(410, $entity->httpStatusCode);
        $this->assertNull($entity->redirectUri);
    }

    #[TestDox('findAll() returns all changed website URI entities')]
    public function testFindAllChangedWebsiteUris(): void
    {
        $changedWebsiteUris = $this->repository->findAll();

        $this->assertCount(4, $changedWebsiteUris);
        $this->assertContainsOnlyInstancesOf(ChangedWebsiteUri::class, $changedWebsiteUris);
    }

    #[TestDox('findBy() throws exception')]
    public function testFindByThrowsException(): void
    {
        $this->expectException(LogicException::class);

        $this->repository->findBy([]);
    }

    #[TestDox('findOneBy() throws exception')]
    public function testFindOneByThrowsException(): void
    {
        $this->expectException(LogicException::class);

        $this->repository->findOneBy([]);
    }
}
