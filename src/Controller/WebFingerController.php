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

namespace App\Controller;

use App\Entity\WebFingerAccount;
use App\Repository\WebFingerAccountRepository;
use App\Util\CacheTtl;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

use function array_map;
use function count;
use function explode;
use function in_array;
use function strtolower;
use function trim;
use function urldecode;

/**
 * Implements the WebFinger protocol
 *
 * @link https://www.rfc-editor.org/rfc/rfc7033.html RFC 7033: WebFinger
 */
#[AsController]
#[Route('/.well-known/webfinger', 'app_webfinger')]
#[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
final readonly class WebFingerController
{
    private const array HEADERS = [
        'access-control-allow-origin' => '*',
        'content-type' => 'application/jrd+json; charset=utf-8',
    ];

    public function __construct(
        private WebFingerAccountRepository $repository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $resource = $request->query->getString('resource');

        if (trim($resource) === '') {
            return new Response('{}', Response::HTTP_BAD_REQUEST, self::HEADERS);
        }

        $account = $this->repository->findOneBy(['hostname' => $request->getHost(), 'account' => $resource]);

        if ($account === null) {
            return new Response('{}', Response::HTTP_NOT_FOUND, self::HEADERS);
        }

        return new JsonResponse(data: $this->buildData($account, $request), headers: self::HEADERS);
    }

    /**
     * @return array{
     *     subject: string,
     *     aliases: list<string>,
     *     properties: array<string, string|null>,
     *     links: list<array<string, string>>,
     * }
     */
    private function buildData(WebFingerAccount $account, Request $request): array
    {
        $requestedRelations = $this->parseRelations($request);

        $links = [];
        foreach ($account->links as $link) {
            foreach ($link->getRels() as $rel) {
                if (count($requestedRelations) > 0 && !in_array($rel, $requestedRelations)) {
                    continue;
                }

                $links[] = [
                    'rel' => $rel,
                    'href' => $link->getHref(),
                    ...$link->getAttributes(),
                ];
            }
        }

        return [
            'subject' => $account->subject,
            'aliases' => $account->aliases,
            'properties' => $account->properties,
            'links' => $links,
        ];
    }

    /**
     * @return list<string>
     */
    private function parseRelations(Request $request): array
    {
        /** @var string $queryString */
        $queryString = $request->server->get('QUERY_STRING', '');

        $parts = array_map(
            function (string $value): array {
                [$k, $v] = explode('=', $value, 2);

                return ['key' => urldecode($k), 'value' => urldecode($v)];
            },
            explode('&', $queryString),
        );

        $relations = [];
        foreach ($parts as $part) {
            if (strtolower($part['key']) === 'rel') {
                $relations[] = $part['value'];
            }
        }

        return $relations;
    }
}
