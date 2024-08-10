<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\Repository\AuthorRepository;
use App\Service\Entity\AuthorManager;
use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('AuthorManager')]
class AuthorManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AuthorRepository & MockInterface $repository;
    private AuthorManager $manager;
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->repository = Mockery::mock(AuthorRepository::class);
        $this->manager = new AuthorManager($this->repository);
    }

    #[TestDox('creates an empty author instance')]
    public function testCreateEmptyAuthorInstance(): void
    {
        $byline = $this->faker->name();
        $email = $this->faker->safeEmail();

        $author = $this->manager->createAuthor($byline, $email);

        $this->assertSame($byline, $author->getByline());
        $this->assertSame($email, $author->getEmail());
        $this->assertInstanceOf(DateTimeImmutable::class, $author->getCreatedAt());
        $this->assertNull($author->getUpdatedAt());
        $this->assertNull($author->getDeletedAt());
        $this->assertNull($author->getUser());
        $this->assertEmpty($author->getPosts());
    }

    #[TestDox('::getRepository() returns an AuthorRepository')]
    public function testGetRepository(): void
    {
        $this->assertSame($this->repository, $this->manager->getRepository());
    }
}
