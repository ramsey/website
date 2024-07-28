<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostTag;
use App\Entity\ShortUrl;
use App\Entity\User;
use App\Tests\DataFixtures\PostFixtures;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function assert;
use function implode;
use function strlen;

class PostTest extends KernelTestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    #[Group('db')]
    #[TestDox('is fully populated when retrieved from the database')]
    public function testEntityFromDatabase(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Post::class);

        $post = $repository->findOneBy(['slug' => PostFixtures::SLUG1]);
        assert($post instanceof Post);

        $this->assertInstanceOf(UuidInterface::class, $post->getId());
        $this->assertInstanceOf(User::class, $post->getAuthor());
        $this->assertIsString($post->getTitle());
        $this->assertGreaterThan(0, strlen($post->getTitle()));
        $this->assertSame(PostFixtures::SLUG1, $post->getSlug());
        $this->assertSame([PostCategory::Blog], $post->getCategory());
        $this->assertIsString($post->getDescription());
        $this->assertGreaterThan(0, strlen($post->getDescription()));
        $this->assertIsString($post->getKeywords());
        $this->assertGreaterThan(0, strlen($post->getKeywords()));
        $this->assertIsString($post->getFeedId());
        $this->assertGreaterThan(0, strlen($post->getFeedId()));
        $this->assertSame(PostBodyType::Html, $post->getBodyType());
        $this->assertIsString($post->getBody());
        $this->assertGreaterThan(0, strlen($post->getBody()));
        $this->assertIsString($post->getExcerpt());
        $this->assertFalse($post->getShortUrls()->isEmpty());
        $this->assertFalse($post->getTags()->isEmpty());
        $this->assertGreaterThan(0, strlen($post->getExcerpt()));
        $this->assertInstanceOf(DateTimeImmutable::class, $post->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $post->getUpdatedAt());
        $this->assertNull($post->getDeletedAt());
    }

    #[TestDox('sets the author')]
    public function testSetName(): void
    {
        $user = new User();
        $post = new Post();

        $this->assertSame($post, $post->setAuthor($user));
        $this->assertSame($user, $post->getAuthor());
    }

    #[TestDox('sets the body')]
    public function testSetBody(): void
    {
        $body = $this->faker->text();
        $post = new Post();

        $this->assertSame($post, $post->setBody($body));
        $this->assertSame($body, $post->getBody());
    }

    #[TestDox('sets the body type')]
    public function testSetBodyType(): void
    {
        $post = new Post();

        $this->assertSame($post, $post->setBodyType(PostBodyType::ReStructuredText));
        $this->assertSame(PostBodyType::ReStructuredText, $post->getBodyType());
    }

    #[TestDox('sets the category')]
    public function testSetCategory(): void
    {
        $post = new Post();

        $this->assertSame($post, $post->setCategory([PostCategory::Blog]));
        $this->assertSame([PostCategory::Blog], $post->getCategory());
    }

    #[TestDox('sets the description')]
    public function testSetDescription(): void
    {
        $description = $this->faker->sentence();
        $post = new Post();

        $this->assertSame($post, $post->setDescription($description));
        $this->assertSame($description, $post->getDescription());
    }

    #[TestDox('sets the excerpt')]
    public function testSetExcerpt(): void
    {
        $excerpt = $this->faker->sentence();
        $post = new Post();

        $this->assertSame($post, $post->setExcerpt($excerpt));
        $this->assertSame($excerpt, $post->getExcerpt());
    }

    #[TestDox('sets the feed ID')]
    public function testSetFeedId(): void
    {
        $feedId = $this->faker->uuid();
        $post = new Post();

        $this->assertSame($post, $post->setFeedId($feedId));
        $this->assertSame($feedId, $post->getFeedId());
    }

    #[TestDox('sets the ID')]
    public function testSetId(): void
    {
        $id = Uuid::uuid7();
        $post = new Post();

        $this->assertSame($post, $post->setId($id));
        $this->assertSame($id, $post->getId());
    }

    #[TestDox('allows "overwriting" an existing ID with the same ID')]
    public function testOverwritingId(): void
    {
        $id1 = Uuid::uuid7();
        $id2 = Uuid::fromBytes($id1->getBytes());
        $post = new Post();

        $this->assertSame($post, $post->setId($id1));
        $this->assertSame($id1, $post->getId());
        $this->assertSame($post, $post->setId($id2));
        $this->assertNotSame($id1, $post->getId());
        $this->assertSame($id2, $post->getId());
    }

    #[TestDox('throws an exception when attempting to overwrite an existing ID')]
    public function testThrowsWhenOverwritingId(): void
    {
        $id1 = Uuid::uuid7();
        $id2 = Uuid::uuid7();
        $post = new Post();

        $this->assertSame($post, $post->setId($id1));
        $this->assertSame($id1, $post->getId());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot overwrite an existing ID with a different ID');

        $post->setId($id2);
    }

    #[TestDox('sets keywords')]
    public function testSetKeywords(): void
    {
        $keywords = implode(',', (array) $this->faker->words());
        $post = new Post();

        $this->assertSame($post, $post->setKeywords($keywords));
        $this->assertSame($keywords, $post->getKeywords());
    }

    #[TestDox('sets the slug')]
    public function testSetSlug(): void
    {
        $slug = $this->faker->slug();
        $post = new Post();

        $this->assertSame($post, $post->setSlug($slug));
        $this->assertSame($slug, $post->getSlug());
    }

    #[TestDox('sets the title')]
    public function testSetTitle(): void
    {
        $title = $this->faker->sentence();
        $post = new Post();

        $this->assertSame($post, $post->setTitle($title));
        $this->assertSame($title, $post->getTitle());
    }

    #[TestDox('adds and removes tags')]
    public function testAssociatingTagsWithPosts(): void
    {
        $tag1 = new PostTag();
        $tag2 = new PostTag();
        $tag3 = new PostTag();
        $post = new Post();

        $this->assertTrue($post->getTags()->isEmpty());
        $this->assertSame($post, $post->addTag($tag1));
        $this->assertSame($post, $post->addTag($tag2));
        $this->assertSame($post, $post->addTag($tag3));

        // Attempting to add the same tag is a no-op.
        $this->assertSame($post, $post->addTag($tag2));

        $this->assertCount(3, $post->getTags());
        $this->assertTrue($post->getTags()->contains($tag1));
        $this->assertTrue($post->getTags()->contains($tag2));
        $this->assertTrue($post->getTags()->contains($tag3));

        // Attempt to remove a tag.
        $this->assertSame($post, $post->removeTag($tag2));
        $this->assertCount(2, $post->getTags());
        $this->assertTrue($post->getTags()->contains($tag1));
        $this->assertFalse($post->getTags()->contains($tag2));
        $this->assertTrue($post->getTags()->contains($tag3));
    }

    #[TestDox('adds and removes short URLs')]
    public function testAssociatingShortUrlsWithPosts(): void
    {
        $shortUrl1 = new ShortUrl();
        $shortUrl2 = new ShortUrl();
        $post = new Post();

        $this->assertTrue($post->getShortUrls()->isEmpty());
        $this->assertSame($post, $post->addShortUrl($shortUrl1));
        $this->assertSame($post, $post->addShortUrl($shortUrl2));

        // Attempting to add the same short URL is a no-op.
        $this->assertSame($post, $post->addShortUrl($shortUrl1));

        $this->assertCount(2, $post->getShortUrls());
        $this->assertTrue($post->getShortUrls()->contains($shortUrl1));
        $this->assertTrue($post->getShortUrls()->contains($shortUrl2));

        // Attempt to remove a short URL.
        $this->assertSame($post, $post->removeShortUrl($shortUrl1));
        $this->assertCount(1, $post->getShortUrls());
        $this->assertFalse($post->getShortUrls()->contains($shortUrl1));
        $this->assertTrue($post->getShortUrls()->contains($shortUrl2));
    }
}
