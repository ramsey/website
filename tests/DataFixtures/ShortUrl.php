<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Service\ShortUrlManager;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ShortUrl extends Fixture
{
    public function __construct(private readonly ShortUrlManager $shortUrlManager)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $shortUrl = $this->shortUrlManager
            ->createShortUrl('https://example.com/this-is-a-long-url')
            ->setCreatedAt(new DateTime('14 days ago'))
            ->setUpdatedAt(new DateTime('last week'))
            ->setDeletedAt(new DateTime('yesterday'));
        $manager->persist($shortUrl);

        $shortUrl = $this->shortUrlManager
            ->createShortUrl('https://example.com/this-is-a-long-url')
            ->setSlug('F0084R');
        $manager->persist($shortUrl);

        $shortUrl = $this->shortUrlManager
            ->createShortUrl('https://example.com/another-long-url', 'custom1')
            ->setCreatedAt(new DateTime('13 days ago'))
            ->setUpdatedAt(new DateTime('6 days ago'));
        $manager->persist($shortUrl);

        $shortUrl = $this->shortUrlManager
            ->createShortUrl('https://example.com/another-long-url', 'custom2');
        $manager->persist($shortUrl);

        $manager->flush();
    }
}
