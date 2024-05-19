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

use App\Entity\ChangedWebsiteUri;
use Doctrine\Persistence\ObjectRepository;
use LogicException;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function is_string;
use function str_ends_with;
use function substr;

/**
 * @implements ObjectRepository<ChangedWebsiteUri>
 */
final readonly class ChangedWebsiteUriRepository implements ObjectRepository
{
    public function __construct(
        #[Autowire('%app.data.changed_website_uri%')] private string $dataPath,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    public function find(mixed $id): ?ChangedWebsiteUri
    {
        if (!is_string($id)) {
            return null;
        }

        // Remove the trailing "/index.html" or "/" so we can do easier matching.
        if (str_ends_with($id, '/index.html')) {
            $id = substr($id, 0, -11);
        } elseif (str_ends_with($id, '/')) {
            $id = substr($id, 0, -1);
        }

        $data = $this->getData()[$id] ?? null;

        if ($data === null) {
            return null;
        }

        return new ChangedWebsiteUri(
            $this->uriFactory->createUri($id),
            $data['httpStatusCode'],
            isset($data['redirectUri']) ? $this->uriFactory->createUri($data['redirectUri']) : null,
        );
    }

    /**
     * @return ChangedWebsiteUri[]
     */
    public function findAll(): iterable
    {
        $uris = [];

        foreach ($this->getData() as $uri => $data) {
            $uris[] = new ChangedWebsiteUri(
                $this->uriFactory->createUri($uri),
                $data['httpStatusCode'],
                isset($data['redirectUri']) ? $this->uriFactory->createUri($data['redirectUri']) : null,
            );
        }

        return $uris;
    }

    /**
     * @inheritDoc
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): never
    {
        throw new LogicException('This method is not supported.');
    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $criteria): never
    {
        throw new LogicException('This method is not supported.');
    }

    public function getClassName(): string
    {
        return ChangedWebsiteUri::class;
    }

    /**
     * @return array<string, array{httpStatusCode: int, redirectUri?: string}>
     */
    private function getData(): array
    {
        return require $this->dataPath;
    }
}
