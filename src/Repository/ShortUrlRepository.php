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

namespace App\Repository;

use App\Entity\ShortUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function trim;

/**
 * @extends ServiceEntityRepository<ShortUrl>
 */
class ShortUrlRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UriFactoryInterface $uriFactory,
        #[Autowire('%app.shortener.hostname%')] private readonly string $hostname,
    ) {
        parent::__construct($registry, ShortUrl::class);
    }

    public function findOneByCustomSlug(string $customSlug): ?ShortUrl
    {
        /** @var ShortUrl|null */
        return $this->createQueryBuilder('s')
            ->andWhere('s.deletedAt IS NULL')
            ->andWhere('s.customSlug = :customSlug')
            ->setParameter('customSlug', $customSlug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneBySlug(string $slug): ?ShortUrl
    {
        /** @var ShortUrl|null */
        return $this->createQueryBuilder('s')
            ->andWhere('s.deletedAt IS NULL')
            ->andWhere('s.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds the ShortUrl entity matching the given short URL string or UriInterface instance
     *
     * @param UriInterface | string $shortUrl A string short URL used to look up a ShortUrl entity
     */
    public function getShortUrlForShortUrl(UriInterface | string $shortUrl): ?ShortUrl
    {
        if (!$shortUrl instanceof UriInterface) {
            $shortUrl = $this->uriFactory->createUri($shortUrl);
        }

        if ($shortUrl->getHost() !== $this->hostname) {
            return null;
        }

        $path = trim($shortUrl->getPath(), '/');

        return $this->findOneByCustomSlug($path) ?? $this->findOneBySlug($path);
    }
}
