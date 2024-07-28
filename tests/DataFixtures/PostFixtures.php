<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostTag;
use App\Entity\ShortUrl;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

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
            PostTagFixtures::class,
            ShortUrlFixtures::class,
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::USER);

        /** @var PostTag $tag1 */
        $tag1 = $this->getReference(PostTagFixtures::TAG1);

        /** @var PostTag $tag2 */
        $tag2 = $this->getReference(PostTagFixtures::TAG2);

        /** @var ShortUrl $shortUrl */
        $shortUrl = $this->getReference(ShortUrlFixtures::SHORT_URL1);

        $post1 = (new Post())
            ->setAuthor($user)
            ->setTitle($this->faker->sentence())
            ->setSlug(self::SLUG1)
            ->setCategory([PostCategory::Blog])
            ->addTag($tag1)
            ->addTag($tag2)
            ->setDescription($this->faker->sentence())
            ->setKeywords((array) $this->faker->words(5))
            ->setBodyType(PostBodyType::Html)
            ->setBody($this->faker->text())
            ->setExcerpt($this->faker->sentence())
            ->addShortUrl($shortUrl)
            ->setFeedId($this->faker->uuid())
            ->setCreatedAt(new DateTimeImmutable())
            ->setCreatedBy($user)
            ->setUpdatedAt(new DateTimeImmutable())
            ->setUpdatedBy($user)
            ->setMetadata(['foo' => 1234, 'bar' => 'abcd', 'baz' => null]);

        $manager->persist($post1);

        $manager->flush();
    }
}
