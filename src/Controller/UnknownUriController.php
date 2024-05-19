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

use App\Repository\ChangedWebsiteUriRepository;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Twig\Environment;

use function str_starts_with;

#[AsController]
final readonly class UnknownUriController
{
    public function __construct(
        private ChangedWebsiteUriRepository $repository,
        private Environment $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $changedWebsiteUri = $this->repository->find($request->getPathInfo());

        $status = $changedWebsiteUri->httpStatusCode ?? Response::HTTP_NOT_FOUND;
        $redirectUri = $this->canonicalizeRedirectUri($changedWebsiteUri->redirectUri ?? null, $request);

        $template = match (true) {
            $status >= 300 && $status < 400 => 'error/redirect.html.twig',
            $status === 410 => 'error/gone.html.twig',
            default => 'error/not-found.html.twig',
        };

        $headers = [];
        if ($redirectUri !== null) {
            $headers['location'] = $redirectUri;
        }

        $body = $this->twig->render($template, [
            'status' => $status,
            'uri' => $redirectUri,
        ]);

        return new Response($body, $status, $headers);
    }

    private function canonicalizeRedirectUri(?UriInterface $uri, Request $request): ?string
    {
        if ($uri === null) {
            return null;
        }

        $stringUri = (string) $uri;

        if (!str_starts_with($stringUri, 'http://') && !str_starts_with($stringUri, 'https://')) {
            $stringUri = $request->getUriForPath($stringUri);
        }

        return $stringUri;
    }
}
