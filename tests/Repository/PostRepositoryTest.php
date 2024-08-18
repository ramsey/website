<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Tests\DataFixtures\PostFixtures;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[TestDox('PostRepository')]
#[Group('db')]
class PostRepositoryTest extends KernelTestCase
{
    private PostRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(Post::class);
    }

    #[TestDox('::findOneByYearMonthSlug() returns a record from the database')]
    public function testFindOneByYearMonthSlug(): void
    {
        $post = $this->repository->findOneByYearMonthSlug('2024', '08', PostFixtures::SLUG1);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame('01913f38-fe0b-7220-bc2a-bea9e990d181', $post->getId()->toString());
    }

    #[TestDox('::findOneByYearMonthSlug() returns a record from the database, using integers')]
    public function testFindOneByYearMonthSlugWithIntegers(): void
    {
        $post = $this->repository->findOneByYearMonthSlug(2024, 8, PostFixtures::SLUG1);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame('01913f38-fe0b-7220-bc2a-bea9e990d181', $post->getId()->toString());
    }

    #[TestDox('::findOneByYearMonthSlug() returns null when it can\'t find a record')]
    public function testFindOneByYearMonthSlugWithWrongMonth(): void
    {
        $this->assertNull($this->repository->findOneByYearMonthSlug('2024', '07', PostFixtures::SLUG1));
        $this->assertNull($this->repository->findOneByYearMonthSlug('2024', '09', PostFixtures::SLUG1));
    }

    #[TestDox('::findOneByYearMonthSlug() returns null when the date is invalid')]
    public function testFindOneByYearMonthSlugWithInvalidDate(): void
    {
        $this->assertNull($this->repository->findOneByYearMonthSlug('2024', 26, PostFixtures::SLUG1));
    }
}
