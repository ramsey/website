<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\OpenPgpWebKey;
use App\Repository\OpenPgpWebKeyRepository;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class OpenPgpWebKeyRepositoryTest extends TestCase
{
    private OpenPgpWebKeyRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new OpenPgpWebKeyRepository(
            __DIR__ . '/fixtures/openpgp_web_key.php',
        );
    }

    #[TestDox('getClassName() returns App\Entity\OpenPgpWebKey')]
    public function testGetClassName(): void
    {
        $this->assertEquals(OpenPgpWebKey::class, $this->repository->getClassName());
    }

    #[TestDox('find() throws exception')]
    public function testFindThrowsException(): void
    {
        $this->expectException(LogicException::class);

        $this->repository->find('foo');
    }

    #[TestDox('findAll() returns all OpenPGP web key entities')]
    public function testFindAll(): void
    {
        $openPgpWebKeys = $this->repository->findAll();

        $this->assertCount(3, $openPgpWebKeys);
        $this->assertContainsOnlyInstancesOf(OpenPgpWebKey::class, $openPgpWebKeys);
    }

    #[TestDox('findBy() with hostname returns expected values')]
    public function testFindByHostname(): void
    {
        $openPgpWebKeys1 = $this->repository->findBy(['hostname' => 'one.example.com']);
        $openPgpWebKeys2 = $this->repository->findBy(['hostname' => 'two.example.com']);

        $this->assertCount(2, $openPgpWebKeys1);
        $this->assertCount(1, $openPgpWebKeys2);
        $this->assertContainsOnlyInstancesOf(OpenPgpWebKey::class, $openPgpWebKeys1);
        $this->assertContainsOnlyInstancesOf(OpenPgpWebKey::class, $openPgpWebKeys2);

        $this->assertSame('one.example.com', $openPgpWebKeys1[0]->hostname);
        $this->assertSame('localpart1', $openPgpWebKeys1[0]->localPart);
        $this->assertSame('Zm9v', $openPgpWebKeys1[0]->base64EncodedKey);

        $this->assertSame('one.example.com', $openPgpWebKeys1[1]->hostname);
        $this->assertSame('localpart2', $openPgpWebKeys1[1]->localPart);
        $this->assertSame('YmFy', $openPgpWebKeys1[1]->base64EncodedKey);

        $this->assertSame('two.example.com', $openPgpWebKeys2[0]->hostname);
        $this->assertSame('localpart1', $openPgpWebKeys2[0]->localPart);
        $this->assertSame('YmF6', $openPgpWebKeys2[0]->base64EncodedKey);
    }

    #[TestDox('findBy() with localPart returns expected values')]
    public function testFindByLocalPart(): void
    {
        $openPgpWebKeys1 = $this->repository->findBy(['localPart' => 'localPart1']);
        $openPgpWebKeys2 = $this->repository->findBy(['localPart' => 'localPart2']);

        $this->assertCount(2, $openPgpWebKeys1);
        $this->assertCount(1, $openPgpWebKeys2);
        $this->assertContainsOnlyInstancesOf(OpenPgpWebKey::class, $openPgpWebKeys1);
        $this->assertContainsOnlyInstancesOf(OpenPgpWebKey::class, $openPgpWebKeys2);

        $this->assertSame('one.example.com', $openPgpWebKeys1[0]->hostname);
        $this->assertSame('localpart1', $openPgpWebKeys1[0]->localPart);
        $this->assertSame('Zm9v', $openPgpWebKeys1[0]->base64EncodedKey);

        $this->assertSame('two.example.com', $openPgpWebKeys1[1]->hostname);
        $this->assertSame('localpart1', $openPgpWebKeys1[1]->localPart);
        $this->assertSame('YmF6', $openPgpWebKeys1[1]->base64EncodedKey);

        $this->assertSame('one.example.com', $openPgpWebKeys2[0]->hostname);
        $this->assertSame('localpart2', $openPgpWebKeys2[0]->localPart);
        $this->assertSame('YmFy', $openPgpWebKeys2[0]->base64EncodedKey);
    }

    #[TestDox('findBy() with hostname and localPart returns expected values')]
    public function testFindByHostnameAndLocalPart(): void
    {
        $openPgpWebKeys1 = $this->repository->findBy(['hostname' => 'one.example.com', 'localPart' => 'localPart1']);

        $this->assertCount(1, $openPgpWebKeys1);
        $this->assertContainsOnlyInstancesOf(OpenPgpWebKey::class, $openPgpWebKeys1);

        $this->assertSame('one.example.com', $openPgpWebKeys1[0]->hostname);
        $this->assertSame('localpart1', $openPgpWebKeys1[0]->localPart);
        $this->assertSame('Zm9v', $openPgpWebKeys1[0]->base64EncodedKey);
    }

    #[TestDox('findBy() returns empty array when no entities found')]
    public function testFindByReturnsEmptyArray(): void
    {
        $keys = $this->repository->findBy(['hostname' => 'foo.example.com']);

        $this->assertEmpty($keys);
    }

    #[TestDox('findOneBy() returns only one entity')]
    public function testFindOneBy(): void
    {
        $openPgpWebKey = $this->repository->findOneBy(['hostname' => 'one.example.com', 'localPart' => 'localPart1']);

        $this->assertNotNull($openPgpWebKey);
        $this->assertSame('one.example.com', $openPgpWebKey->hostname);
        $this->assertSame('localpart1', $openPgpWebKey->localPart);
        $this->assertSame('Zm9v', $openPgpWebKey->base64EncodedKey);
    }

    #[TestDox('findOneBy() returns null when an entity is not found')]
    public function testFindOneByReturnsNullWhenNoEntityFound(): void
    {
        $openPgpWebKey = $this->repository->findOneBy(['hostname' => 'one.example.com', 'localPart' => 'foobar']);

        $this->assertNull($openPgpWebKey);
    }

    #[TestDox('findOneBy() returns null when either criteria are not provided')]
    public function testFindOneByWithoutEitherCriteria(): void
    {
        // @phpstan-ignore argument.type
        $key1 = $this->repository->findOneBy(['hostname' => 'one.example.com']);

        // @phpstan-ignore argument.type
        $key2 = $this->repository->findOneBy(['localPart' => 'localPart1']);

        $this->assertNull($key1);
        $this->assertNull($key2);
    }
}
