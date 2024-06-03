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

use App\Repository\ShortUrlRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;

use function str_starts_with;
use function substr;

#[AsController]
#[Cache(maxage: 90, public: false)]
final readonly class ShortUrlController
{
    private const string NOT_FOUND_BODY = <<<'EOD'
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Not Found</title>
            </head>
            <body>
                <h1>Not Found</h1>
            </body>
        </html>
        EOD;

    public function __construct(private ShortUrlRepository $shortUrlRepository)
    {
    }

    public function __invoke(Request $request): Response
    {
        $slug = $request->getPathInfo();

        if (str_starts_with($slug, '/su/')) {
            $slug = substr($slug, 4);
        } else {
            // Trim the leading forward slash.
            $slug = substr($slug, 1);
        }

        $shortUrl = $this->shortUrlRepository->findOneByCustomSlug($slug)
            ?? $this->shortUrlRepository->findOneBySlug($slug);

        if ($shortUrl === null) {
            return new Response(self::NOT_FOUND_BODY, Response::HTTP_NOT_FOUND);
        }

        return new RedirectResponse(
            url: (string) $shortUrl->getDestinationUrl(),
            headers: [
                'content-security-policy' => 'referrer always;',
                'location' => $shortUrl->getDestinationUrl(),
                'referrer-policy' => 'unsafe-url',
            ],
        );
    }
}
