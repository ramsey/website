<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\PostTag;
use App\Repository\PostTagRepository;
use App\Tests\DataFixtures\PostTagFixtures;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('db')]
#[TestDox('PostTagRepository')]
class PostTagRepositoryTest extends KernelTestCase
{
    private PostTagRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(PostTag::class);
    }

    #[TestDox('returns a tag by name')]
    public function testFindOneByName(): void
    {
        $tag = $this->repository->findOneByName(PostTagFixtures::TAG1);

        $this->assertSame(PostTagFixtures::TAG1, $tag?->getName());
        $this->assertInstanceOf(UuidInterface::class, $tag->getId());
    }

    #[TestDox('returns null when a tag cannot be found by name')]
    public function testFindOneByNameReturnsNull(): void
    {
        $tag = $this->repository->findOneByName('not-a-tag-in-the-database');

        $this->assertNull($tag);
    }
}
