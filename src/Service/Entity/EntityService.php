<?php

declare(strict_types=1);

namespace App\Service\Entity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template TKey of array-key
 * @template T of object
 */
interface EntityService
{
    /**
     * Returns the repository for this service
     *
     * @return ObjectRepository<T> & Selectable<TKey, T> & ServiceEntityRepositoryInterface
     */
    public function getRepository(): ObjectRepository & Selectable & ServiceEntityRepositoryInterface;
}
