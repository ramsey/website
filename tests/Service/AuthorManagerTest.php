<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\AuthorRepository;
use App\Service\AuthorManager;
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

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(AuthorRepository::class);
        $this->manager = new AuthorManager($this->repository);
    }

    #[TestDox('creates an empty author instance')]
    public function testCreateEmptyAuthorInstance(): void
    {
        $author = $this->manager->createAuthor();

        $this->assertNull($author->getUser());
        $this->assertNull($author->getUpdatedAt());
        $this->assertNull($author->getDeletedAt());
        $this->assertEmpty($author->getPosts());
    }

    #[TestDox('::getRepository() returns an AuthorRepository')]
    public function testGetRepository(): void
    {
        $this->assertSame($this->repository, $this->manager->getRepository());
    }
}
