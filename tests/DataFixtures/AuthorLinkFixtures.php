<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\Author;
use App\Entity\AuthorLink;
use App\Entity\AuthorLinkType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Laminas\Diactoros\UriFactory;

class AuthorLinkFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @return list<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            AuthorFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $uriFactory = new UriFactory();

        /** @var Author $author1 */
        $author1 = $this->getReference(AuthorFixtures::AUTHOR1);
        $author1
            ->addLink(
                (new AuthorLink())
                    ->setType(AuthorLinkType::LinkedIn)
                    ->setUrl($uriFactory->createUri($faker->url())),
            )
            ->addLink(
                (new AuthorLink())
                    ->setType(AuthorLinkType::GitHub)
                    ->setUrl($uriFactory->createUri($faker->url())),
            );

        $manager->persist($author1);

        /** @var Author $author2 */
        $author2 = $this->getReference(AuthorFixtures::AUTHOR2);
        $author2
            ->addLink(
                (new AuthorLink())
                    ->setType(AuthorLinkType::Mastodon)
                    ->setUrl($uriFactory->createUri($faker->url())),
            )
            ->addLink(
                (new AuthorLink())
                    ->setType(AuthorLinkType::Mastodon)
                    ->setUrl($uriFactory->createUri($faker->url())),
            )
            ->addLink(
                (new AuthorLink())
                    ->setType(AuthorLinkType::Website)
                    ->setUrl($uriFactory->createUri($faker->url())),
            )
            ->addLink(
                (new AuthorLink())
                    ->setType(AuthorLinkType::SpeakerDeck)
                    ->setUrl($uriFactory->createUri($faker->url())),
            );

        $manager->persist($author2);

        $manager->flush();
    }
}
