<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostTag;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\PostManager;
use DateTimeImmutable;
use Faker\Factory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class PostManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private PostRepository & MockInterface $repository;
    private PostManager $manager;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(PostRepository::class);
        $this->manager = new PostManager($this->repository);
    }

    #[TestDox('creates a new post instance with the given values')]
    public function testCreatePost(): void
    {
        $faker = Factory::create();

        $title = $faker->sentence();
        $slug = $faker->slug();
        $category = [PostCategory::Blog];
        $type = PostBodyType::Markdown;
        $body = $faker->text();
        $user = new User();

        $tag1 = new PostTag();
        $tag2 = new PostTag();
        $tag3 = new PostTag();

        $post = $this->manager->createPost($title, $slug, $category, $type, $body, $user, [$tag1, $tag2, $tag3]);

        $this->assertSame($title, $post->getTitle());
        $this->assertSame($slug, $post->getSlug());
        $this->assertSame($category, $post->getCategory());
        $this->assertSame($type, $post->getBodyType());
        $this->assertSame($body, $post->getBody());
        $this->assertSame($user, $post->getAuthor());
        $this->assertInstanceOf(DateTimeImmutable::class, $post->getCreatedAt());
        $this->assertSame($user, $post->getCreatedBy());
        $this->assertInstanceOf(DateTimeImmutable::class, $post->getUpdatedAt());
        $this->assertSame($user, $post->getUpdatedBy());
        $this->assertNull($post->getDeletedAt());
        $this->assertCount(3, $post->getTags());
        $this->assertTrue($post->getTags()->contains($tag1));
        $this->assertTrue($post->getTags()->contains($tag2));
        $this->assertTrue($post->getTags()->contains($tag3));
    }

    #[TestDox('::getRepository() returns a PostRepository')]
    public function testGetRepository(): void
    {
        $this->assertSame($this->repository, $this->manager->getRepository());
    }
}
