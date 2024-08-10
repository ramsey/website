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
        /** @var Author $author1 */
        $author1 = $this->getReference(AuthorFixtures::AUTHOR1);

        /** @var Author $author2 */
        $author2 = $this->getReference(AuthorFixtures::AUTHOR2);

        /** @var Author $author3 */
        $author3 = $this->getReference(AuthorFixtures::AUTHOR3);

        /** @var PostTag $tag1 */
        $tag1 = $this->getReference(PostTagFixtures::TAG1);

        /** @var PostTag $tag2 */
        $tag2 = $this->getReference(PostTagFixtures::TAG2);

        /** @var ShortUrl $shortUrl */
        $shortUrl = $this->getReference(ShortUrlFixtures::SHORT_URL1);

        $post1Id = Uuid::uuid7();
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
            ->setCreatedAt(new DateTimeImmutable())
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
            ->setFeedId($post2Id->getUrn())
            ->setCreatedAt(new DateTimeImmutable());
        $manager->persist($post2);

        $manager->flush();
    }
}
