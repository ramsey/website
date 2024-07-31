<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\PostTag;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class PostTagFixtures extends Fixture
{
    public const string TAG1 = 'fun-things';
    public const string TAG2 = 'more-fun-things';
    public const string TAG3 = 'even-more-fun-things';

    public function load(ObjectManager $manager): void
    {
        $tag1 = (new PostTag())
            ->setName(self::TAG1)
            ->setCreatedAt(new DateTimeImmutable('-3 weeks'));
        $manager->persist($tag1);

        $tag2 = (new PostTag())
            ->setName(self::TAG2)
            ->setCreatedAt(new DateTimeImmutable('-2 weeks'))
            ->setUpdatedAt(new DateTimeImmutable('-1 weeks'));
        $manager->persist($tag2);

        $tag3 = (new PostTag())
            ->setName(self::TAG3)
            ->setCreatedAt(new DateTimeImmutable('-2 weeks'))
            ->setUpdatedAt(new DateTimeImmutable('-1 weeks'))
            ->setDeletedAt(new DateTimeImmutable('-3 days'));
        $manager->persist($tag3);

        $manager->flush();

        $this->addReference(self::TAG1, $tag1);
        $this->addReference(self::TAG2, $tag2);
        $this->addReference(self::TAG3, $tag3);
    }
}
