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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class RobotsController
{
    private const string ADS_TXT = "placeholder.example.com, placeholder, DIRECT, placeholder\n";

    public function __construct(private Environment $twig)
    {
    }

    #[Route('/robots.txt')]
    #[Cache(maxage: CacheTtl::Day->value, public: true, mustRevalidate: true)]
    public function robots(): Response
    {
        return new Response(
            content: $this->twig->render('robots/robots.txt'),
            headers: ['content-type' => 'text/plain; charset=utf-8'],
        );
    }

    /**
     * Support for ads.txt and app-ads.txt to deny injection of ads by malware
     *
     * See section 3.2.1 of the Ads.txt Version 1.1 specification, for more details.
     *
     * @link https://iabtechlab.com/ads-txt/ Ads.txt - Authorized Digital Sellers
     */
    #[Route('/ads.txt')]
    #[Route('/app-ads.txt')]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function ads(): Response
    {
        return new Response(
            content: self::ADS_TXT,
            headers: ['content-type' => 'text/plain; charset=utf-8'],
        );
    }
}
