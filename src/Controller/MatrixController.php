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

use App\Util\CacheTtl;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use function strtolower;

#[AsController]
final readonly class MatrixController
{
    #[Route('/.well-known/matrix/client')]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function client(Request $request): Response
    {
        $data = [
            'm.homeserver' => [
                'base_url' => 'https://matrix.ramsey.dev',
                'server_name' => 'ramsey.dev',
            ],
            'm.identity_server' => [
                'base_url' => 'https://vector.im',
            ],
        ];

        return match (strtolower($request->getHost())) {
            'ramsey.dev', 'localhost',
                '127.0.0.1' => new JsonResponse(data: $data, headers: ['access-control-allow-origin' => '*']),
            default => throw new NotFoundHttpException(),
        };
    }

    #[Route('/.well-known/matrix/server')]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function server(Request $request): Response
    {
        $data = [
            'm.server' => 'matrix.ramsey.dev:443',
        ];

        return match (strtolower($request->getHost())) {
            'ramsey.dev', 'localhost',
                '127.0.0.1' => new JsonResponse(data: $data, headers: ['access-control-allow-origin' => '*']),
            default => throw new NotFoundHttpException(),
        };
    }
}
