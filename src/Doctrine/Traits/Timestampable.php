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

namespace App\Doctrine\Traits;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

trait Timestampable
{
    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @phpstan-assert !null $this->getCreatedAt()
     */
    public function setCreatedAt(DateTimeInterface $createdAt = new DateTimeImmutable()): static
    {
        // Do not overwrite the createdAt date, if it is already set.
        if ($this->createdAt === null) {
            $this->createdAt = DateTimeImmutable::createFromInterface($createdAt);
        }

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @phpstan-assert !null $this->getUpdatedAt()
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt = new DateTimeImmutable()): static
    {
        $this->updatedAt = DateTimeImmutable::createFromInterface($updatedAt);

        return $this;
    }

    #[ORM\PrePersist]
    public static function prePersist(PrePersistEventArgs $eventArgs): void
    {
        /** @var static $entity */
        $entity = $eventArgs->getObject();

        $entity->setCreatedAt();
    }

    #[ORM\PreUpdate]
    public static function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        /** @var static $entity */
        $entity = $eventArgs->getObject();

        $entity->setUpdatedAt();
    }
}
