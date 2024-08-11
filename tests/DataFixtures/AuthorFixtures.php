<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\Author;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class AuthorFixtures extends Fixture implements DependentFixtureInterface
{
    public const string AUTHOR1 = 'author1';
    public const string AUTHOR2 = 'author2';
    public const string AUTHOR3 = 'author3';

    /**
     * @return list<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /** @var User $user */
        $user = $this->getReference(UserFixtures::USER);

        $author1 = (new Author())
            ->setByline($faker->name())
            ->setEmail('author1@example.com')
            ->setUser($user)
            ->setCreatedAt(new DateTimeImmutable('-4 weeks'));
        $manager->persist($author1);

        $author2 = (new Author())
            ->setByline($faker->name())
            ->setEmail('author2@example.com')
            ->setCreatedAt(new DateTimeImmutable('-3 weeks'));
        $manager->persist($author2);

        $author3 = (new Author())
            ->setByline($faker->name())
            ->setEmail($faker->safeEmail())
            ->setCreatedAt(new DateTimeImmutable('-2 weeks'));
        $manager->persist($author3);

        $manager->flush();

        $this->addReference(self::AUTHOR1, $author1);
        $this->addReference(self::AUTHOR2, $author2);
        $this->addReference(self::AUTHOR3, $author3);
    }
}
