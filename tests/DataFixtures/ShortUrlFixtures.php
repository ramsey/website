<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\User;
use App\Service\ShortUrlManager;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ShortUrlFixtures extends Fixture implements DependentFixtureInterface
{
    public const string SHORT_URL1 = 'short-url-1';

    public function __construct(private readonly ShortUrlManager $shortUrlManager)
    {
    }

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
        /** @var User $superAdminUser */
        $superAdminUser = $this->getReference(UserFixtures::SUPER_ADMIN_USER);

        $shortUrl1 = $this->shortUrlManager
            ->createShortUrl('https://example.com/this-is-a-long-url', $superAdminUser)
            ->setCreatedAt(new DateTime('14 days ago'))
            ->setUpdatedAt(new DateTime('last week'))
            ->setDeletedAt(new DateTime('yesterday'));
        $manager->persist($shortUrl1);

        $shortUrl2 = $this->shortUrlManager
            ->createShortUrl('https://example.com/this-is-a-long-url', $superAdminUser)
            ->setSlug('F0084R')
            ->setCustomSlug('this-is-a-custom-slug');
        $manager->persist($shortUrl2);

        $shortUrl3 = $this->shortUrlManager
            ->createShortUrl('https://example.com/another-long-url', $superAdminUser, 'custom1')
            ->setCreatedAt(new DateTime('13 days ago'))
            ->setUpdatedAt(new DateTime('6 days ago'));
        $manager->persist($shortUrl3);

        $shortUrl4 = $this->shortUrlManager
            ->createShortUrl('https://example.com/another-long-url', $superAdminUser, 'custom2');
        $manager->persist($shortUrl4);

        $manager->flush();

        $this->addReference(self::SHORT_URL1, $shortUrl1);
    }
}
