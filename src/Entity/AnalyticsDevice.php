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

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteable;
use App\Doctrine\Traits\Timestampable;
use App\Repository\AnalyticsDeviceRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

/**
 * A device (i.e., client), as recorded by an analytics event
 */
#[ORM\Entity(repositoryClass: AnalyticsDeviceRepository::class)]
#[ORM\UniqueConstraint(fields: ['brandName', 'category', 'device', 'engine', 'family', 'name', 'osFamily'])]
class AnalyticsDevice
{
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface $id;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $brandName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    private string $device;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $engine = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $family = null;

    #[ORM\Column]
    private bool $isBot = false;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $osFamily = null;

    /**
     * Returns the device's database identifier
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns the device's brand name
     */
    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    /**
     * Sets the device's brand name
     */
    public function setBrandName(?string $brandName): static
    {
        $this->brandName = $brandName;

        return $this;
    }

    /**
     * Returns the device's category
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * Sets the device's category
     */
    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Returns the device type
     */
    public function getDevice(): string
    {
        return $this->device;
    }

    /**
     * Sets the device type
     */
    public function setDevice(string $device): static
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Returns the device's engine name
     */
    public function getEngine(): ?string
    {
        return $this->engine;
    }

    /**
     * Sets the device's engine name
     */
    public function setEngine(?string $engine): static
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Returns the device's family name
     */
    public function getFamily(): ?string
    {
        return $this->family;
    }

    /**
     * Sets the device's family name
     */
    public function setFamily(?string $family): static
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Returns true if the device is a bot
     */
    public function isBot(): bool
    {
        return $this->isBot;
    }

    /**
     * Sets whether the device is a bot
     */
    public function setBot(bool $isBot): static
    {
        $this->isBot = $isBot;

        return $this;
    }

    /**
     * Returns the device's name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the device's name
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the device's operating system family name
     */
    public function getOsFamily(): ?string
    {
        return $this->osFamily;
    }

    /**
     * Sets the device's operating system family name
     */
    public function setOsFamily(?string $osFamily): static
    {
        $this->osFamily = $osFamily;

        return $this;
    }
}
