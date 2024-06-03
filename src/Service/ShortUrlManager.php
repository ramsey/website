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

namespace App\Service;

use App\Entity\ShortUrl;
use App\Repository\ShortUrlRepository;
use App\Service\Codec\Base62Codec;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function preg_match;
use function random_bytes;

final readonly class ShortUrlManager
{
    public function __construct(
        #[Autowire('%app.shortener.base_url%')] private string $baseUrl,
        public ShortUrlRepository $repository,
        private UriFactoryInterface $uriFactory,
        private Base62Codec $codec,
    ) {
    }

    public function buildUrl(ShortUrl $shortUrl): UriInterface
    {
        if ($shortUrl->getCustomSlug() !== null) {
            return $this->uriFactory->createUri($this->baseUrl . $shortUrl->getCustomSlug());
        }

        return $this->uriFactory->createUri($this->baseUrl . $shortUrl->getSlug());
    }

    public function createShortUrl(string $url, ?string $customSlug = null): ShortUrl
    {
        $this->checkCustomSlug($customSlug);

        $shortUrl = (new ShortUrl())
            ->setDestinationUrl($this->uriFactory->createUri($url))
            ->setSlug($this->getRandomSlug())
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        if ($customSlug !== null) {
            $shortUrl->setCustomSlug($customSlug);
        }

        return $shortUrl;
    }

    /**
     * @phpstan-assert !null $shortUrl->getDeletedAt()
     * @phpstan-impure
     */
    public function softDeleteShortUrl(ShortUrl $shortUrl): ShortUrl
    {
        $shortUrl->setDeletedAt(new DateTimeImmutable());

        return $shortUrl;
    }

    private function checkCustomSlug(?string $customSlug): void
    {
        if ($customSlug === null) {
            return;
        }

        if (preg_match('/^[a-z0-9\-_.]+$/i', $customSlug)) {
            return;
        }

        throw new InvalidArgumentException("Invalid custom slug: $customSlug");
    }

    private function getRandomSlug(): string
    {
        do {
            $randomSlug = $this->codec->encode(random_bytes(5));
            $shortUrl = $this->repository->findOneBySlug($randomSlug);
        } while ($shortUrl !== null);

        return $randomSlug;
    }
}
