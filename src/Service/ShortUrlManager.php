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
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use InvalidArgumentException;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function count;
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
        $shortUrl = $this->checkUrl($url, $customSlug);

        if ($shortUrl && ($shortUrl->getCustomSlug() !== null || $customSlug === null)) {
            return $shortUrl;
        }

        $this->checkCustomSlug($customSlug);

        $shortUrl = $shortUrl ?? new ShortUrl();
        $shortUrl->setDestinationUrl($this->uriFactory->createUri($url));

        if ($shortUrl->getSlug() === null) {
            $shortUrl->setSlug($this->getRandomSlug());
        }

        if ($customSlug !== null) {
            $shortUrl->setCustomSlug($customSlug);
        }

        if ($shortUrl->getCreatedAt() === null) {
            $shortUrl->setCreatedAt(new DateTimeImmutable());
        }

        $shortUrl->setUpdatedAt(new DateTimeImmutable());

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

    private function checkUrl(string $url, ?string $customSlug): ?ShortUrl
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->eq('destinationUrl', $url));
        $criteria->where(Criteria::expr()->isNull('deletedAt'));

        /** @var Collection<int, ShortUrl> $shortUrls */
        $shortUrls = $this->repository->matching($criteria);

        if (count($shortUrls) === 0) {
            return null;
        }

        if ($customSlug !== null) {
            // Return the first short URL that matches the custom slug.
            foreach ($shortUrls as $shortUrl) {
                if ($shortUrl->getCustomSlug() === $customSlug) {
                    return $shortUrl;
                }
            }

            // We didn't find one that matched the custom slug, so let's find
            // one that does not have a custom slug and return it. We will
            // update it with the custom slug.
            foreach ($shortUrls as $shortUrl) {
                if ($shortUrl->getCustomSlug() === null) {
                    return $shortUrl;
                }
            }

            // We could not match the custom slug, and we couldn't find one
            // without a custom slug, so we'll return null and create a new one.
            return null;
        }

        // We're not attempting to set a custom slug, so if we have short URLs,
        // return the first one with a custom slug and use it.
        foreach ($shortUrls as $shortUrl) {
            if ($shortUrl->getCustomSlug() !== null) {
                return $shortUrl;
            }
        }

        // We couldn't find a short URL with a custom slug, so return the first
        // one we find.
        return $shortUrls[0];
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
