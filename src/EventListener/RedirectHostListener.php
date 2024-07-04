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

namespace App\EventListener;

use App\Controller\ShortUrlController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

use function str_starts_with;
use function strtolower;
use function substr;

/**
 * All of this logic used to be handled within an Apache .htaccess file, but
 * I've moved it here to handle at the application level so that I can change
 * web servers and maintain the same behavior without needing to configure the
 * behavior using the new web server's configuration flavor of choice.
 */
#[AsEventListener(event: 'kernel.request', priority: 200)]
final readonly class RedirectHostListener
{
    private bool $isTestEnv;
    private bool $isDevEnv;
    private bool $isDevTestEnv;

    public function __construct(
        #[Autowire('%kernel.environment%')] private string $environment,
        private Environment $twig,
    ) {
        $this->isTestEnv = ($this->environment === 'test');
        $this->isDevEnv = ($this->environment === 'dev');
        $this->isDevTestEnv = ($this->isTestEnv || $this->isDevEnv);
    }

    public function __invoke(RequestEvent $event): void
    {
        // If this is the "dev" or "test" environment, don't check hostnames.
        if ($this->isDevTestEnv) {
            return;
        }

        $host = strtolower($event->getRequest()->getHost());
        $path = $event->getRequest()->getRequestUri();

        // If we're already on ben.ramsey.dev, then everything is good, and
        // do not redirect for the /health route.
        if ($host === 'ben.ramsey.dev' || $path === '/health') {
            return;
        }

        // If the host is for short URLs and the short URL controller is already set, then we're good.
        if ($host === 'bram.se' && $event->getRequest()->attributes->get('_controller') === ShortUrlController::class) {
            return;
        }

        if ($this->redirectForWww($event, $host, $path)) {
            return;
        }

        if ($this->allowOpenPgpKeyHost($event, $host, $path)) {
            return;
        }

        if ($this->isRamseyDevResource($event, $host, $path)) {
            return;
        }

        if ($this->isBenRamseyComResource($host, $path)) {
            return;
        }

        // For everything else, do a permanent redirect.
        $event->setResponse(new RedirectResponse(
            'https://ben.ramsey.dev' . $path,
            Response::HTTP_MOVED_PERMANENTLY,
        ));
    }

    /**
     * Checks whether the host begins with "www," and if so, instructs the
     * kernel to perform a permanent redirect to the hostname without the
     * leading "www."
     */
    private function redirectForWww(RequestEvent $event, string $host, string $path): bool
    {
        if (!str_starts_with($host, 'www.')) {
            return false;
        }

        $event->setResponse(new RedirectResponse(
            'https://' . substr($host, 4) . $path,
            Response::HTTP_MOVED_PERMANENTLY,
        ));

        return true;
    }

    /**
     * Checks whether this is an openpgpkey host, and if so, it allows requests
     * to resources under "/.well-known/openpgpkey/" but forbids requests to
     * all other paths.
     */
    private function allowOpenPgpKeyHost(RequestEvent $event, string $host, string $path): bool
    {
        if ($host !== 'openpgpkey.ramsey.dev' && $host !== 'openpgpkey.benramsey.com') {
            return false;
        }

        if (!str_starts_with($path, '/.well-known/openpgpkey/')) {
            $event->setResponse(new Response(
                $this->twig->render('error/forbidden.html.twig'),
                Response::HTTP_FORBIDDEN,
            ));
        }

        return true;
    }

    /**
     * Checks whether the hostname is ramsey.dev, and if so, allows requests to
     * specific resources for that host and performs appropriate redirection for
     * other resources.
     */
    private function isRamseyDevResource(RequestEvent $event, string $host, string $path): bool
    {
        if ($host !== 'ramsey.dev') {
            return false;
        }

        if (
            str_starts_with($path, '/.well-known/matrix/')
            || str_starts_with($path, '/.well-known/openpgpkey/')
            || str_starts_with($path, '/.well-known/webfinger')
        ) {
            return true;
        }

        if (str_starts_with($path, '/_matrix') || str_starts_with($path, '/_synapse')) {
            $event->setResponse(new RedirectResponse(
                'https://matrix.ramsey.dev' . $path,
                Response::HTTP_PERMANENTLY_REDIRECT,
            ));

            return true;
        }

        $event->setResponse(new RedirectResponse(
            'https://ben.ramsey.dev' . $path,
            Response::HTTP_FOUND,
        ));

        return true;
    }

    /**
     * Checks whether the hostname is benramsey.com, and if so, allows requests
     * to specific resources for that host.
     */
    private function isBenRamseyComResource(string $host, string $path): bool
    {
        if ($host !== 'benramsey.com') {
            return false;
        }

        return str_starts_with($path, '/.well-known/keybase.txt')
            || str_starts_with($path, '/.well-known/openpgpkey/')
            || str_starts_with($path, '/.well-known/webfinger');
    }
}
