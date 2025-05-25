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

use App\Entity\WebFingerAccount;
use Doctrine\Persistence\ObjectRepository;
use LogicException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\WebLink\Link;

use function strtolower;

/**
 * @phpstan-type WebLink array{
 *     href: string,
 *     rel?: string,
 *     type?: string,
 * }
 * @phpstan-type WebFingerResource array{
 *     subject: string,
 *     aliases?: list<string>,
 *     properties?: array<string, string>,
 *     links?: list<WebLink>,
 * }
 * @implements ObjectRepository<WebFingerAccount>
 */
final readonly class WebFingerAccountRepository implements ObjectRepository
{
    public function __construct(
        #[Autowire('%app.data.webfinger_account%')] private string $dataPath,
    ) {
    }

    public function find(mixed $id): never
    {
        throw new LogicException('This method is not supported.');
    }

    /**
     * @return WebFingerAccount[]
     */
    public function findAll(): array
    {
        $records = [];

        foreach ($this->getData() as $hostname => $accounts) {
            foreach ($accounts as $account => $data) {
                $records[] = new WebFingerAccount(
                    $hostname,
                    $account,
                    $data['subject'] ?? throw new RuntimeException('subject is required'),
                    $this->buildLinks($data['links'] ?? []),
                    $data['properties'] ?? [],
                    $data['aliases'] ?? [],
                );
            }
        }

        return $records;
    }

    /**
     * @param array{hostname?: string, account?: string} $criteria
     *
     * @inheritDoc
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $found = [];

        foreach ($this->findAll() as $account) {
            if (isset($criteria['hostname']) && strtolower($criteria['hostname']) !== $account->hostname) {
                continue;
            }

            if (isset($criteria['account']) && strtolower($criteria['account']) !== $account->account) {
                continue;
            }

            $found[] = $account;
        }

        return $found;
    }

    /**
     * @param array{hostname: string, account: string} $criteria
     *
     * @inheritDoc
     */
    public function findOneBy(array $criteria): ?WebFingerAccount
    {
        if (!isset($criteria['hostname']) || !isset($criteria['account'])) {
            return null;
        }

        $accounts = $this->findBy($criteria);

        return $accounts[0] ?? null;
    }

    public function getClassName(): string
    {
        return WebFingerAccount::class;
    }

    /**
     * @return array<string, array<string, WebFingerResource>>
     */
    private function getData(): array
    {
        return require $this->dataPath;
    }

    /**
     * @return Link[]
     *
     * @phpstan-param WebLink[] $linksData
     */
    private function buildLinks(array $linksData): array
    {
        $links = [];

        foreach ($linksData as $link) {
            $links[] = $this->buildLink($link);
        }

        return $links;
    }

    /**
     * @phpstan-param WebLink $linkData
     */
    private function buildLink(array $linkData): Link
    {
        $webLink = new Link($linkData['rel'] ?? null, $linkData['href']);

        if (isset($linkData['type'])) {
            $webLink = $webLink->withAttribute('type', $linkData['type']);
        }

        return $webLink;
    }
}
