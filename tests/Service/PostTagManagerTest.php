<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\PostTagRepository;
use App\Service\PostTagManager;
use DateTimeImmutable;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[TestDox('PostTagManager')]
class PostTagManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private PostTagRepository & MockInterface $repository;
    private PostTagManager $manager;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(PostTagRepository::class);
        $this->manager = new PostTagManager($this->repository);
    }

    #[TestDox('creates a new tag instance with the given tag name')]
    public function testCreateTag(): void
    {
        $tag = $this->manager->createTag('test_tag');

        $this->assertSame('test_tag', $tag->getName());
        $this->assertInstanceOf(DateTimeImmutable::class, $tag->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $tag->getUpdatedAt());
        $this->assertNull($tag->getDeletedAt());
        $this->assertEmpty($tag->getPosts());
    }

    #[TestDox('validates the tag name when creating a new tag')]
    #[TestWith(['foo', true])]
    #[TestWith(['FOO', true])]
    #[TestWith(['FooBar', true])]
    #[TestWith(['Foo123Bar', true])]
    #[TestWith(['foo_bar', true])]
    #[TestWith(['foo-bar', true])]
    #[TestWith(['foo.bar', true])]
    #[TestWith(['foo:bar', true])]
    #[TestWith(['foo+bar', true])]
    #[TestWith(['foo?bar', false])]
    #[TestWith(['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', true])]
    #[TestWith(['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', false])]
    public function testValidTagNames(string $tagName, bool $expectValid): void
    {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Tag name is invalid: $tagName");
        }

        $tag = $this->manager->createTag($tagName);

        if ($expectValid) {
            $this->assertSame($tagName, $tag->getName());
        }
    }

    #[TestDox('::getRepository() returns a PostTagRepository')]
    public function testGetRepository(): void
    {
        $this->assertSame($this->repository, $this->manager->getRepository());
    }
}
