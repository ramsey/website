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
use App\Repository\AnalyticsEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

/**
 * An analytics event, e.g. a page view
 */
#[ORM\Entity(repositoryClass: AnalyticsEventRepository::class)]
#[ORM\Index(fields: ['hostname', 'name'])]
class AnalyticsEvent
{
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface $id;

    #[ManyToOne(targetEntity: AnalyticsDevice::class)]
    #[JoinColumn(name: 'device_id', referencedColumnName: 'id')]
    private AnalyticsDevice $device;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $geoCity = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $geoCountryCode = null;

    #[ORM\Column(nullable: true)]
    private ?float $geoLatitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $geoLongitude = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $geoSubdivisionCode = null;

    #[ORM\Column(length: 50)]
    private string $hostname;

    #[ORM\Column(type: 'binary', length: 20)]
    private string $ipUserAgentHash;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $redirectUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referrer = null;

    /**
     * @var array<string, array<string, scalar> | scalar | null>
     */
    #[ORM\Column(options: ['jsonb' => true])]
    private array $tags = [];

    #[ORM\Column(length: 255)]
    private string $uri;

    #[ORM\Column(length: 255)]
    private string $userAgent;

    /**
     * Returns the event's database identifier
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns the device details for the client that originated the event
     */
    public function getDevice(): AnalyticsDevice
    {
        return $this->device;
    }

    /**
     * Sets the device details for the client that originated the event
     */
    public function setDevice(AnalyticsDevice $device): static
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Returns the geographic city for the client that originated the event
     */
    public function getGeoCity(): ?string
    {
        return $this->geoCity;
    }

    /**
     * Sets the geographic city for the client that originated the event
     */
    public function setGeoCity(?string $geoCity): static
    {
        $this->geoCity = $geoCity;

        return $this;
    }

    /**
     * Returns the geographic country code for the client that originated the event
     */
    public function getGeoCountryCode(): ?string
    {
        return $this->geoCountryCode;
    }

    /**
     * Sets the geographic country code for the client that originated the event
     */
    public function setGeoCountryCode(?string $geoCountryCode): static
    {
        $this->geoCountryCode = $geoCountryCode;

        return $this;
    }

    /**
     * Returns the geographic latitude for the client that originated the event
     */
    public function getGeoLatitude(): ?float
    {
        return $this->geoLatitude;
    }

    /**
     * Sets the geographic latitude for the client that originated the event
     */
    public function setGeoLatitude(?float $geoLatitude): static
    {
        $this->geoLatitude = $geoLatitude;

        return $this;
    }

    /**
     * Returns the geographic longitude for the client that originated the event
     */
    public function getGeoLongitude(): ?float
    {
        return $this->geoLongitude;
    }

    /**
     * Sets the geographic longitude for the client that originated the event
     */
    public function setGeoLongitude(?float $geoLongitude): static
    {
        $this->geoLongitude = $geoLongitude;

        return $this;
    }

    /**
     * Returns the geographic subdivision code (e.g., state, province, etc.) for the client that originated the event
     */
    public function getGeoSubdivisionCode(): ?string
    {
        return $this->geoSubdivisionCode;
    }

    /**
     * Sets the geographic subdivision code (e.g., state, province, etc.) for the client that originated the event
     */
    public function setGeoSubdivisionCode(?string $geoSubdivisionCode): static
    {
        $this->geoSubdivisionCode = $geoSubdivisionCode;

        return $this;
    }

    /**
     * Returns the hostname where the event occurred
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Sets the hostname where the event occurred
     */
    public function setHostname(string $hostname): static
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Returns the hash of the IP address and user agent that originated the event
     *
     * @return string A hash as a 16-bit binary string
     */
    public function getIpUserAgentHash(): string
    {
        return $this->ipUserAgentHash;
    }

    /**
     * Sets the hash of the IP address and user agent that originated the event
     *
     * @param string $ipUserAgentHash The hash as a 16-bit binary string
     */
    public function setIpUserAgentHash(string $ipUserAgentHash): static
    {
        $this->ipUserAgentHash = $ipUserAgentHash;

        return $this;
    }

    /**
     * Returns the locale for the client that originated the event, if available
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Sets the locale for the client that originated the event, if available
     */
    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns the name of the event
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of the event
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the URL redirected to, if available
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the URL redirected to
     */
    public function setRedirectUrl(?string $redirectUrl): static
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Returns the referring URL, if available
     */
    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    /**
     * Sets the referring URL
     */
    public function setReferrer(?string $referrer): static
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Returns all tags associated with the event
     *
     * @return array<string, array<string, scalar> | scalar | null>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Sets tags associated with the event
     *
     * This method will overwrite existing tags on the event.
     *
     * @param array<string, array<string, scalar> | scalar | null> $tags
     */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Returns the URI that originated the event
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Sets the URI that originated the event
     */
    public function setUri(string $uri): static
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Returns the user agent that originated the event
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Sets the user agent that originated the event
     */
    public function setUserAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }
}
