<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\Author;
use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostStatus;
use App\Entity\PostTag;
use App\Entity\ShortUrl;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

final class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public const string SLUG1 = 'a-beautiful-day-in-the-neighborhood';

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @return list<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            AuthorFixtures::class,
            PostTagFixtures::class,
            ShortUrlFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $author1 = $this->getReference(AuthorFixtures::AUTHOR1, Author::class);
        $author2 = $this->getReference(AuthorFixtures::AUTHOR2, Author::class);
        $author3 = $this->getReference(AuthorFixtures::AUTHOR3, Author::class);
        $tag1 = $this->getReference(PostTagFixtures::TAG1, PostTag::class);
        $tag2 = $this->getReference(PostTagFixtures::TAG2, PostTag::class);
        $shortUrl = $this->getReference(ShortUrlFixtures::SHORT_URL1, ShortUrl::class);

        $post1Id = Uuid::fromString('01913f38-fe0b-7220-bc2a-bea9e990d181');
        $post1 = (new Post())
            ->setId($post1Id)
            ->addAuthor($author1)
            ->addAuthor($author2)
            ->setTitle($this->faker->sentence())
            ->setSlug(self::SLUG1)
            ->setCategory([PostCategory::Blog])
            ->setStatus(PostStatus::Published)
            ->addTag($tag1)
            ->addTag($tag2)
            ->setDescription($this->faker->sentence())
            ->setKeywords((array) $this->faker->words(5))
            ->setBodyType(PostBodyType::Html)
            ->setBody($this->faker->text())
            ->setExcerpt($this->faker->sentence())
            ->addShortUrl($shortUrl)
            ->setFeedId($post1Id->getUrn())
            ->setCreatedAt(new DateTimeImmutable('2024-08-08 13:32:45 +00:00'))
            ->setUpdatedAt(new DateTimeImmutable('2024-08-21 00:15:02 -05:00'))
            ->setPublishedAt(new DateTimeImmutable('2024-08-11 21:34:03 -04:00'))
            ->setModifiedAt(new DateTimeImmutable('2024-08-18 01:22:33 +02:00'))
            ->setMetadata(['foo' => 1234, 'bar' => 'abcd', 'baz' => null]);
        $manager->persist($post1);

        $post2Id = Uuid::uuid7();
        $post2 = (new Post())
            ->setId($post2Id)
            ->addAuthor($author3)
            ->setTitle($this->faker->sentence())
            ->setSlug($this->faker->slug())
            ->setCategory([PostCategory::Blog])
            ->setStatus(PostStatus::Draft)
            ->addTag($tag1)
            ->setDescription($this->faker->sentence())
            ->setKeywords((array) $this->faker->words(5))
            ->setBodyType(PostBodyType::Html)
            ->setBody($this->faker->text())
            ->setExcerpt($this->faker->sentence())
            ->setFeedId($post2Id->getUrn());
        $manager->persist($post2);

        $manager->flush();
    }
}
