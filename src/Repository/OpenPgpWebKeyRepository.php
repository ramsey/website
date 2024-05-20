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

use App\Entity\OpenPgpWebKey;
use Doctrine\Persistence\ObjectRepository;
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function strtolower;

/**
 * @implements ObjectRepository<OpenPgpWebKey>
 */
final readonly class OpenPgpWebKeyRepository implements ObjectRepository
{
    public function __construct(
        #[Autowire('%app.data.openpgp_web_key%')] private string $dataPath,
    ) {
    }

    public function find(mixed $id): never
    {
        throw new LogicException('This method is not supported.');
    }

    /**
     * @inheritDoc
     */
    public function findAll(): iterable
    {
        $keys = [];

        foreach ($this->getData() as $hostname => $data) {
            foreach ($data as $localPart => $key) {
                $keys[] = new OpenPgpWebKey($hostname, $localPart, $key);
            }
        }

        return $keys;
    }

    /**
     * @param array{hostname?: string, localPart?: string} $criteria
     *
     * @inheritDoc
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $found = [];

        foreach ($this->findAll() as $key) {
            if (isset($criteria['hostname']) && strtolower($criteria['hostname']) !== $key->hostname) {
                continue;
            }

            if (isset($criteria['localPart']) && strtolower($criteria['localPart']) !== $key->localPart) {
                continue;
            }

            $found[] = $key;
        }

        return $found;
    }

    /**
     * @param array{hostname?: string, localPart?: string} $criteria
     */
    public function findOneBy(array $criteria): ?OpenPgpWebKey
    {
        $keys = $this->findBy($criteria);

        return $keys[0] ?? null;
    }

    public function getClassName(): string
    {
        return OpenPgpWebKey::class;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getData(): array
    {
        return require $this->dataPath;
    }
}
