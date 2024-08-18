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

use App\Entity\Post;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

use function sprintf;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findOneByYearMonthSlug(int | string $year, int | string $month, string $slug): ?Post
    {
        try {
            $startDate = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
            $endDate = $startDate->modify('+1 month');
        } catch (Throwable) {
            return null;
        }

        /** @var Post | null */
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.createdAt >= :startDate')
            ->andWhere('p.createdAt < :endDate')
            ->setParameter('slug', $slug)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
