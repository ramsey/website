<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostTag;
use App\Entity\ShortUrl;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\Blog\ParsedPost;
use App\Service\Blog\ParsedPostMetadata;
use App\Service\PostManager;
use App\Service\PostTagService;
use App\Service\ShortUrlService;
use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PostManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Generator $faker;
    private PostRepository & MockInterface $repository;
    private PostManager $manager;
    private PostTagService & MockInterface $tagService;
    private ShortUrlService & MockInterface $shortUrlService;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->tagService = Mockery::mock(PostTagService::class);
        $this->shortUrlService = Mockery::mock(ShortUrlService::class);
        $this->repository = Mockery::mock(PostRepository::class);
        $this->manager = new PostManager($this->repository, $this->tagService, $this->shortUrlService);
    }

    #[TestDox('creates a new post instance with the given values')]
    public function testCreatePost(): void
    {
        $title = $this->faker->sentence();
        $slug = $this->faker->slug();
        $category = [PostCategory::Blog];
        $type = PostBodyType::Markdown;
        $body = $this->faker->text();
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
        $this->assertSame([], $post->getMetadata());
        $this->assertSame([], $post->getKeywords());
        $this->assertNull($post->getDescription());
        $this->assertNull($post->getExcerpt());
        $this->assertTrue($post->getShortUrls()->isEmpty());
    }

    #[TestDox('::getRepository() returns a PostRepository')]
    public function testGetRepository(): void
    {
        $this->assertSame($this->repository, $this->manager->getRepository());
    }

    public function testCreateFromParsedPost(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                categories: [PostCategory::Blog],
                tags: ['tag1', 'tag2'],
                description: $this->faker->sentence(),
                keywords: (array) $this->faker->words(5),
                excerpt: $this->faker->text(),
                feedId: $this->faker->url(),
                additional: ['foo' => 'abc', 'bar' => 456, 'shorturl' => 'https://bram.se/short-url'],
                createdAt: new DateTimeImmutable(),
                updatedAt: new DateTimeImmutable(),
            ),
            $this->faker->text(),
        );

        $user = new User();

        $tag1 = new PostTag();
        $tag2 = new PostTag();
        $shortUrl = new ShortUrl();

        $this->tagService->expects('getRepository->findOneByName')->with('tag1')->andReturn($tag1);
        $this->tagService->expects('getRepository->findOneByName')->with('tag2')->andReturnNull();
        $this->tagService->expects('createTag')->with('tag2')->andReturn($tag2);

        $this->shortUrlService
            ->expects('getRepository->getShortUrlForShortUrl')
            ->with('https://bram.se/short-url')
            ->andReturn($shortUrl);

        $post = $this->manager->createFromParsedPost($parsedPost, $user);

        $this->assertSame($parsedPost->metadata->id, $post->getId());
        $this->assertSame($parsedPost->metadata->title, $post->getTitle());
        $this->assertSame($parsedPost->metadata->slug, $post->getSlug());
        $this->assertSame($parsedPost->content, $post->getBody());
        $this->assertSame($parsedPost->metadata->contentType, $post->getBodyType());
        $this->assertSame($parsedPost->metadata->description, $post->getDescription());
        $this->assertSame($parsedPost->metadata->keywords, $post->getKeywords());
        $this->assertSame($parsedPost->metadata->excerpt, $post->getExcerpt());
        $this->assertSame($parsedPost->metadata->feedId, $post->getFeedId());
        $this->assertSame($parsedPost->metadata->additional, $post->getMetadata());
        $this->assertSame($user, $post->getAuthor());
        $this->assertSame($parsedPost->metadata->categories, $post->getCategory());
        $this->assertEquals($parsedPost->metadata->createdAt, $post->getCreatedAt());
        $this->assertSame($user, $post->getCreatedBy());
        $this->assertEquals($parsedPost->metadata->updatedAt, $post->getUpdatedAt());
        $this->assertSame($user, $post->getUpdatedBy());
        $this->assertTrue($post->getTags()->contains($tag1));
        $this->assertTrue($post->getTags()->contains($tag2));
        $this->assertTrue($post->getShortUrls()->contains($shortUrl));
    }

    public function testCreateFromParsedPostWithMinimalData(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: new DateTimeImmutable(),
                updatedAt: null,
            ),
            $this->faker->text(),
        );

        $user = new User();

        $this->tagService->expects('getRepository->findOneByName')->never();
        $this->tagService->expects('createTag')->never();
        $this->shortUrlService->expects('getRepository->getShortUrlForShortUrl')->never();

        $post = $this->manager->createFromParsedPost($parsedPost, $user);

        $this->assertSame($parsedPost->metadata->id, $post->getId());
        $this->assertSame($parsedPost->metadata->title, $post->getTitle());
        $this->assertSame($parsedPost->metadata->slug, $post->getSlug());
        $this->assertSame($parsedPost->content, $post->getBody());
        $this->assertSame($parsedPost->metadata->contentType, $post->getBodyType());
        $this->assertNull($post->getDescription());
        $this->assertSame([], $post->getKeywords());
        $this->assertNull($post->getExcerpt());
        $this->assertNull($post->getFeedId());
        $this->assertSame([], $post->getMetadata());
        $this->assertSame($user, $post->getAuthor());
        $this->assertSame([], $post->getCategory());
        $this->assertEquals($parsedPost->metadata->createdAt, $post->getCreatedAt());
        $this->assertSame($user, $post->getCreatedBy());
        $this->assertEquals($parsedPost->metadata->createdAt, $post->getUpdatedAt());
        $this->assertSame($user, $post->getUpdatedBy());
        $this->assertTrue($post->getTags()->isEmpty());
        $this->assertTrue($post->getShortUrls()->isEmpty());
    }
}
