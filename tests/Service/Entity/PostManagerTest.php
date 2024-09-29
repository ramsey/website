<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\Entity\Author;
use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostStatus;
use App\Entity\PostTag;
use App\Entity\ShortUrl;
use App\Repository\PostRepository;
use App\Service\Blog\ParsedPost;
use App\Service\Blog\ParsedPostAuthor;
use App\Service\Blog\ParsedPostMetadata;
use App\Service\Entity\AuthorService;
use App\Service\Entity\EntityExists;
use App\Service\Entity\PostManager;
use App\Service\Entity\PostTagService;
use App\Service\Entity\ShortUrlService;
use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

use function assert;
use function sprintf;

class PostManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AuthorService & MockInterface $authorService;
    private Generator $faker;
    private PostRepository & MockInterface $repository;
    private PostManager $manager;
    private PostTagService & MockInterface $tagService;
    private ShortUrlService & MockInterface $shortUrlService;

    protected function setUp(): void
    {
        $this->authorService = Mockery::mock(AuthorService::class);
        $this->faker = Factory::create();
        $this->tagService = Mockery::mock(PostTagService::class);
        $this->shortUrlService = Mockery::mock(ShortUrlService::class);
        $this->repository = Mockery::mock(PostRepository::class);
        $this->manager = new PostManager(
            $this->repository,
            $this->tagService,
            $this->shortUrlService,
            $this->authorService,
        );
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
                status: PostStatus::Published,
                categories: [PostCategory::Blog],
                tags: ['tag1', 'tag2'],
                description: $this->faker->sentence(),
                keywords: (array) $this->faker->words(5),
                excerpt: $this->faker->text(),
                feedId: $this->faker->url(),
                additional: ['foo' => 'abc', 'bar' => 456, 'shorturl' => 'https://bram.se/short-url'],
                createdAt: new DateTimeImmutable(),
                publishedAt: new DateTimeImmutable(),
                modifiedAt: new DateTimeImmutable(),
            ),
            $this->faker->text(),
            [
                new ParsedPostAuthor($this->faker->name(), $this->faker->safeEmail()),
                new ParsedPostAuthor($this->faker->name(), $this->faker->safeEmail()),
            ],
        );

        $author1 = new Author();
        $author2 = new Author();
        $tag1 = new PostTag();
        $tag2 = new PostTag();
        $shortUrl = new ShortUrl();

        $this->tagService->expects('getRepository->findOneByName')->with('tag1')->andReturn($tag1);
        $this->tagService->expects('getRepository->findOneByName')->with('tag2')->andReturnNull();
        $this->tagService->expects('createTag')->with('tag2')->andReturn($tag2);

        $this->authorService
            ->expects('getRepository->findOneBy')
            ->with(['email' => $parsedPost->authors[0]->email])
            ->andReturn($author1);
        $this->authorService
            ->expects('getRepository->findOneBy')
            ->with(['email' => $parsedPost->authors[1]->email])
            ->andReturnNull();
        $this->authorService
            ->expects('createAuthor')
            ->with($parsedPost->authors[1]->byline, $parsedPost->authors[1]->email)
            ->andReturn($author2);

        $this->shortUrlService
            ->expects('getRepository->getShortUrlForShortUrl')
            ->with('https://bram.se/short-url')
            ->andReturn($shortUrl);

        $post = $this->manager->createFromParsedPost($parsedPost);

        $this->assertSame($parsedPost->metadata->id, $post->getId());
        $this->assertSame($parsedPost->metadata->title, $post->getTitle());
        $this->assertSame($parsedPost->metadata->slug, $post->getSlug());
        $this->assertSame($parsedPost->metadata->status, $post->getStatus());
        $this->assertSame($parsedPost->content, $post->getBody());
        $this->assertSame($parsedPost->metadata->contentType, $post->getBodyType());
        $this->assertSame($parsedPost->metadata->description, $post->getDescription());
        $this->assertSame($parsedPost->metadata->keywords, $post->getKeywords());
        $this->assertSame($parsedPost->metadata->excerpt, $post->getExcerpt());
        $this->assertSame($parsedPost->metadata->feedId, $post->getFeedId());
        $this->assertSame($parsedPost->metadata->additional, $post->getMetadata());
        $this->assertSame($parsedPost->metadata->categories, $post->getCategory());
        $this->assertEquals($parsedPost->metadata->createdAt, $post->getCreatedAt());
        $this->assertEquals($parsedPost->metadata->publishedAt, $post->getPublishedAt());
        $this->assertEquals($parsedPost->metadata->modifiedAt, $post->getModifiedAt());
        $this->assertTrue($post->getTags()->contains($tag1));
        $this->assertTrue($post->getTags()->contains($tag2));
        $this->assertTrue($post->getShortUrls()->contains($shortUrl));
        $this->assertTrue($post->getAuthors()->contains($author1));
        $this->assertTrue($post->getAuthors()->contains($author2));
    }

    public function testCreateFromParsedPostWithMinimalData(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: null,
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $this->tagService->expects('getRepository->findOneByName')->never();
        $this->tagService->expects('createTag')->never();
        $this->shortUrlService->expects('getRepository->getShortUrlForShortUrl')->never();

        $post = $this->manager->createFromParsedPost($parsedPost);

        $this->assertSame($parsedPost->metadata->id, $post->getId());
        $this->assertSame($parsedPost->metadata->title, $post->getTitle());
        $this->assertSame($parsedPost->metadata->slug, $post->getSlug());
        $this->assertSame($parsedPost->metadata->status, $post->getStatus());
        $this->assertSame($parsedPost->content, $post->getBody());
        $this->assertSame($parsedPost->metadata->contentType, $post->getBodyType());
        $this->assertNull($post->getDescription());
        $this->assertSame([], $post->getKeywords());
        $this->assertNull($post->getExcerpt());
        $this->assertNull($post->getFeedId());
        $this->assertSame([], $post->getMetadata());
        $this->assertTrue($post->getAuthors()->isEmpty());
        $this->assertSame([], $post->getCategory());
        $this->assertNull($post->getCreatedAt());
        $this->assertNull($post->getUpdatedAt());
        $this->assertNull($post->getPublishedAt());
        $this->assertNull($post->getModifiedAt());
        $this->assertTrue($post->getTags()->isEmpty());
        $this->assertTrue($post->getShortUrls()->isEmpty());
    }

    public function testUpdateFromParsedPost(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Deleted,
                categories: [PostCategory::Blog],
                tags: ['tag1', 'tag2'],
                description: $this->faker->sentence(),
                keywords: (array) $this->faker->words(5),
                excerpt: $this->faker->text(),
                feedId: $this->faker->url(),
                additional: ['foo' => 'abc', 'bar' => 456, 'shorturl' => 'https://bram.se/short-url'],
                createdAt: new DateTimeImmutable(),
                publishedAt: new DateTimeImmutable(),
                modifiedAt: new DateTimeImmutable(),
            ),
            $this->faker->text(),
            [
                new ParsedPostAuthor($this->faker->name(), $this->faker->safeEmail()),
            ],
        );

        $author1 = new Author();
        $tag1 = new PostTag();
        $tag2 = new PostTag();
        $shortUrl = new ShortUrl();

        $this->tagService->expects('getRepository->findOneByName')->with('tag1')->andReturn($tag1);
        $this->tagService->expects('getRepository->findOneByName')->with('tag2')->andReturnNull();
        $this->tagService->expects('createTag')->with('tag2')->andReturn($tag2);

        $this->authorService
            ->expects('getRepository->findOneBy')
            ->with(['email' => $parsedPost->authors[0]->email])
            ->andReturn($author1);

        $this->shortUrlService
            ->expects('getRepository->getShortUrlForShortUrl')
            ->with('https://bram.se/short-url')
            ->andReturn($shortUrl);

        assert($parsedPost->metadata->createdAt !== null);

        $post = (new Post())
            ->setId(clone $parsedPost->metadata->id)
            ->setSlug($parsedPost->metadata->slug)
            ->setCreatedAt(clone $parsedPost->metadata->createdAt);

        $post = $this->manager->updateFromParsedPost($post, $parsedPost);

        $this->assertEquals($parsedPost->metadata->id, $post->getId());
        $this->assertSame($parsedPost->metadata->title, $post->getTitle());
        $this->assertSame($parsedPost->metadata->slug, $post->getSlug());
        $this->assertSame($parsedPost->metadata->status, $post->getStatus());
        $this->assertSame($parsedPost->content, $post->getBody());
        $this->assertSame($parsedPost->metadata->contentType, $post->getBodyType());
        $this->assertSame($parsedPost->metadata->description, $post->getDescription());
        $this->assertSame($parsedPost->metadata->keywords, $post->getKeywords());
        $this->assertSame($parsedPost->metadata->excerpt, $post->getExcerpt());
        $this->assertSame($parsedPost->metadata->feedId, $post->getFeedId());
        $this->assertSame($parsedPost->metadata->additional, $post->getMetadata());
        $this->assertSame($parsedPost->metadata->categories, $post->getCategory());
        $this->assertEquals($parsedPost->metadata->createdAt, $post->getCreatedAt());
        $this->assertEquals($parsedPost->metadata->publishedAt, $post->getPublishedAt());
        $this->assertEquals($parsedPost->metadata->modifiedAt, $post->getModifiedAt());
        $this->assertTrue($post->getTags()->contains($tag1));
        $this->assertTrue($post->getTags()->contains($tag2));
        $this->assertTrue($post->getShortUrls()->contains($shortUrl));
        $this->assertTrue($post->getAuthors()->contains($author1));
    }

    public function testUpdateFromParsedPostWithMinimalData(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Hidden,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: null,
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $this->tagService->expects('getRepository->findOneByName')->never();
        $this->tagService->expects('createTag')->never();
        $this->shortUrlService->expects('getRepository->getShortUrlForShortUrl')->never();

        $post = (new Post())
            ->setId(clone $parsedPost->metadata->id)
            ->setSlug($parsedPost->metadata->slug)
            ->setCreatedAt(new DateTimeImmutable());

        $post = $this->manager->updateFromParsedPost($post, $parsedPost);

        $this->assertEquals($parsedPost->metadata->id, $post->getId());
        $this->assertSame($parsedPost->metadata->title, $post->getTitle());
        $this->assertSame($parsedPost->metadata->slug, $post->getSlug());
        $this->assertSame($parsedPost->metadata->status, $post->getStatus());
        $this->assertSame($parsedPost->content, $post->getBody());
        $this->assertSame($parsedPost->metadata->contentType, $post->getBodyType());
        $this->assertNull($post->getDescription());
        $this->assertSame([], $post->getKeywords());
        $this->assertNull($post->getExcerpt());
        $this->assertNull($post->getFeedId());
        $this->assertSame([], $post->getMetadata());
        $this->assertTrue($post->getAuthors()->isEmpty());
        $this->assertSame([], $post->getCategory());
        $this->assertInstanceOf(DateTimeImmutable::class, $post->getCreatedAt());
        $this->assertNull($post->getUpdatedAt());
        $this->assertNull($post->getPublishedAt());
        $this->assertNull($post->getModifiedAt());
        $this->assertTrue($post->getTags()->isEmpty());
        $this->assertTrue($post->getShortUrls()->isEmpty());
    }

    public function testUpdateFromParsedPostThrowsForMismatchedIds(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: null,
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $post = (new Post())
            ->setId(Uuid::uuid7())
            ->setSlug($parsedPost->metadata->slug)
            ->setCreatedAt(new DateTimeImmutable());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to update post with parsed post having a different ID');

        $this->manager->updateFromParsedPost($post, $parsedPost);
    }

    public function testUpdateFromParsedPostThrowsForMismatchedSlugs(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: new DateTimeImmutable(),
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $post = (new Post())
            ->setId(clone $parsedPost->metadata->id)
            ->setSlug('a-different-slug')
            ->setCreatedAt(new DateTimeImmutable());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to update post with parsed post having a different slug');

        $this->manager->updateFromParsedPost($post, $parsedPost);
    }

    #[TestDox('created date in parsed post does not overwrite the post created date')]
    public function testUpdateFromParsedPostWithDifferentCreatedAt(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: new DateTimeImmutable('-2 weeks'),
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $originalPostCreated = new DateTimeImmutable('-3 weeks');

        $post = (new Post())
            ->setId(clone $parsedPost->metadata->id)
            ->setSlug($parsedPost->metadata->slug)
            ->setCreatedAt($originalPostCreated);

        $this->manager->updateFromParsedPost($post, $parsedPost);

        $this->assertEquals($originalPostCreated, $post->getCreatedAt());
    }

    #[TestWith(['cannot have spaces'])]
    #[TestWith(["can't-have-apostrophes"])]
    #[TestWith(['cannot.have.periods'])]
    #[TestWith(['cannot_have_underscores'])]
    #[TestWith(['cannot-have@symbols'])]
    #[TestWith(['cannot-have$symbols'])]
    #[TestWith(['cannot-have\symbols'])]
    #[TestWith(['cannot-have/symbols'])]
    #[TestWith(['cannot-have*symbols'])]
    #[TestWith(['cannot-have%symbols'])]
    #[TestWith(['-cannot-begin-with-dashes'])]
    #[TestWith(['cannot-end-with-dashes-'])]
    public function testInvalidSlug(string $slug): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $slug,
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: null,
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Slug is invalid: $slug");

        $this->manager->createFromParsedPost($parsedPost);
    }

    public function testUpsertFromParsedPostCreatesNewPost(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: null,
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $this->repository->expects('find')->with($parsedPost->metadata->id)->andReturnNull();

        $post = $this->manager->upsertFromParsedPost($parsedPost);

        $this->assertEquals($parsedPost->metadata->id, $post->getId());
        $this->assertSame($parsedPost->metadata->title, $post->getTitle());
        $this->assertSame($parsedPost->metadata->slug, $post->getSlug());
        $this->assertSame($parsedPost->metadata->status, $post->getStatus());
        $this->assertSame($parsedPost->content, $post->getBody());
        $this->assertSame($parsedPost->metadata->contentType, $post->getBodyType());
        $this->assertNull($post->getDescription());
        $this->assertSame([], $post->getKeywords());
        $this->assertNull($post->getExcerpt());
        $this->assertNull($post->getFeedId());
        $this->assertSame([], $post->getMetadata());
        $this->assertTrue($post->getAuthors()->isEmpty());
        $this->assertSame([], $post->getCategory());
        $this->assertNull($post->getCreatedAt());
        $this->assertNull($post->getUpdatedAt());
        $this->assertNull($post->getPublishedAt());
        $this->assertNull($post->getModifiedAt());
        $this->assertTrue($post->getTags()->isEmpty());
        $this->assertTrue($post->getShortUrls()->isEmpty());
    }

    public function testUpsertFromParsedPostThrowsExceptionForExistingPostWithChanges(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: null,
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $post = (new Post())
            ->setId($parsedPost->metadata->id)
            ->setBodyType($parsedPost->metadata->contentType)
            ->setTitle($parsedPost->metadata->title)
            ->setSlug($parsedPost->metadata->slug)
            ->setStatus($parsedPost->metadata->status)
            ->setBody($parsedPost->content . 'Tiny addition to force different content hashes.');

        $this->repository->expects('find')->with($parsedPost->metadata->id)->andReturn($post);

        $this->expectException(EntityExists::class);
        $this->expectExceptionMessage(sprintf(
            "A post with ID '%s' already exists; call %s with TRUE as the second parameter to update the post",
            $parsedPost->metadata->id,
            PostManager::class . '::upsertFromParsedPost',
        ));

        $this->manager->upsertFromParsedPost($parsedPost);
    }

    public function testUpsertFromParsedPostWithExistingPostAndDoUpdateConfirmation(): void
    {
        $parsedPost = new ParsedPost(
            new ParsedPostMetadata(
                id: Uuid::uuid7(),
                contentType: PostBodyType::Markdown,
                title: $this->faker->sentence(),
                slug: $this->faker->slug(),
                status: PostStatus::Draft,
                categories: [],
                tags: [],
                description: null,
                keywords: [],
                excerpt: null,
                feedId: null,
                additional: [],
                createdAt: null,
                publishedAt: null,
                modifiedAt: null,
            ),
            $this->faker->text(),
            [],
        );

        $post = (new Post())
            ->setId($parsedPost->metadata->id)
            ->setBodyType($parsedPost->metadata->contentType)
            ->setTitle($parsedPost->metadata->title)
            ->setSlug($parsedPost->metadata->slug)
            ->setStatus($parsedPost->metadata->status)
            ->setBody($parsedPost->content . 'Tiny addition to force different content hashes.');

        $this->repository->expects('find')->with($parsedPost->metadata->id)->andReturn($post);

        $result = $this->manager->upsertFromParsedPost($parsedPost, doUpdate: true);

        $this->assertSame($post, $result);
        $this->assertSame($parsedPost->content, $post->getBody());
    }
}
