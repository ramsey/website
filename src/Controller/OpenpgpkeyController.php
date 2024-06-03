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

use App\Repository\OpenPgpWebKeyRepository;
use App\Util\CacheTtl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

use function strtolower;

/**
 * Implements OpenPGP Web Key Directory
 *
 * Test the implementation with the following commands:
 *
 *     gpg-wks-client check me@example.com
 *     gpg --locate-keys --auto-key-locate clear,nodefault,wkd me@example.com
 *
 * See the comments in "data/openpgp_web_key.php" for more information.
 *
 * @link https://datatracker.ietf.org/doc/draft-koch-openpgp-webkey-service/
 * @see data/openpgp_web_key.php
 */
#[AsController]
final readonly class OpenpgpkeyController
{
    /** @noinspection SpellCheckingInspection */
    private const string ZBASE32_PATTERN = '[ybndrfg8ejkmcpqxot1uwisza345h769]{32}';

    public function __construct(private OpenPgpWebKeyRepository $repository)
    {
    }

    #[Route('/.well-known/openpgpkey/policy')]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function policy(Request $request): Response
    {
        return $this->policyWithHostname($request->getHost());
    }

    #[Route('/.well-known/openpgpkey/{hostname}/policy')]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function policyWithHostname(string $hostname): Response
    {
        $response = new Response($this->getPolicyDoc($hostname));
        $response->headers->add([
            'access-control-allow-origin' => '*',
            'content-type' => 'text/plain; charset=utf-8',
        ]);

        return $response;
    }

    /**
     * @param string $id The mapped local-part encoded as a z-base-32 string.
     */
    #[Route('/.well-known/openpgpkey/hu/{id}', requirements: ['id' => self::ZBASE32_PATTERN])]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function key(Request $request, string $id): Response
    {
        return $this->keyWithHostname($request->getHost(), $id);
    }

    /**
     * @param string $id The mapped local-part encoded as a z-base-32 string.
     */
    #[Route('/.well-known/openpgpkey/{hostname}/hu/{id}', requirements: ['id' => self::ZBASE32_PATTERN])]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function keyWithHostname(string $hostname, string $id): Response
    {
        $key = $this->repository->findOneBy(['hostname' => $hostname, 'localPart' => $id])
            ?? throw new NotFoundHttpException();

        $response = new Response($key->getRawBinaryKey());
        $response->headers->add([
            'access-control-allow-origin' => '*',
            'content-type' => 'application/octet-stream',
        ]);

        return $response;
    }

    private function getPolicyDoc(string $hostname): string
    {
        return match (strtolower($hostname)) {
            'benramsey.com' => "# Policy flags for domain benramsey.com\n",
            'ramsey.dev' => "# Policy flags for domain ramsey.dev\n",
            default => throw new NotFoundHttpException(),
        };
    }
}
