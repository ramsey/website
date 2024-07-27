<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\PostTag;
use App\Tests\DataFixtures\PostTagFixtures;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function assert;

#[TestDox('PostTag')]
class PostTagTest extends KernelTestCase
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

        $repository = $entityManager->getRepository(PostTag::class);

        $tag = $repository->findOneBy(['name' => PostTagFixtures::TAG1]);
        assert($tag instanceof PostTag);

        $this->assertInstanceOf(UuidInterface::class, $tag->getId());
        $this->assertSame(PostTagFixtures::TAG1, $tag->getName());
        $this->assertInstanceOf(DateTimeImmutable::class, $tag->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $tag->getUpdatedAt());
        $this->assertNull($tag->getDeletedAt());
    }

    #[TestDox('sets the name property')]
    public function testSetName(): void
    {
        $name = $this->faker->slug();

        $tag = new PostTag();

        $this->assertSame($tag, $tag->setName($name));
        $this->assertSame($name, $tag->getName());
    }

    #[TestDox('adds and removes posts, which also adds/removes the tags to/from the posts')]
    public function testAssociatingPostsWithTags(): void
    {
        $post1 = new Post();
        $post2 = new Post();
        $tag = new PostTag();

        $this->assertTrue($tag->getPosts()->isEmpty());
        $this->assertSame($tag, $tag->addPost($post1));
        $this->assertSame($tag, $tag->addPost($post2));

        // Attempting to add the same post is a no-op.
        $this->assertSame($tag, $tag->addPost($post1));

        $this->assertCount(2, $tag->getPosts());
        $this->assertTrue($tag->getPosts()->contains($post1));
        $this->assertTrue($tag->getPosts()->contains($post2));

        // Both posts should have the tag added to them.
        $this->assertTrue($post1->getTags()->contains($tag));
        $this->assertTrue($post2->getTags()->contains($tag));

        // Attempt to remove a post.
        $this->assertSame($tag, $tag->removePost($post1));
        $this->assertCount(1, $tag->getPosts());
        $this->assertFalse($tag->getPosts()->contains($post1));
        $this->assertTrue($tag->getPosts()->contains($post2));
    }

    #[TestDox('sets the createdAt property')]
    public function testSetCreatedAt(): void
    {
        $date = new DateTime();

        $postTag = new PostTag();

        $this->assertSame($postTag, $postTag->setCreatedAt($date));
        $this->assertNotSame($date, $postTag->getCreatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $postTag->getCreatedAt());
        $this->assertSame($date->format('c'), $postTag->getCreatedAt()->format('c'));
    }

    #[TestDox('sets the updatedAt property')]
    public function testSetUpdatedAt(): void
    {
        $date = new DateTime();

        $postTag = new PostTag();

        $this->assertSame($postTag, $postTag->setUpdatedAt($date));
        $this->assertNotSame($date, $postTag->getUpdatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $postTag->getUpdatedAt());
        $this->assertSame($date->format('c'), $postTag->getUpdatedAt()->format('c'));
    }

    #[TestDox('sets the deletedAt property')]
    public function testSetDeletedAt(): void
    {
        $date = new DateTime();

        $postTag = new PostTag();

        $this->assertSame($postTag, $postTag->setDeletedAt($date));
        $this->assertNotSame($date, $postTag->getDeletedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $postTag->getDeletedAt());
        $this->assertSame($date->format('c'), $postTag->getDeletedAt()->format('c'));
    }
}
