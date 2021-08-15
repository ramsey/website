<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function str_starts_with;

class NormalizeBlogUrl implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var int|string $month */
        $month = $request->getAttribute('month');

        $uri = $request->getUri();

        if ((int) $month < 10 && !str_starts_with((string) $month, '0')) {
            $path = '/blog/'
                . (string) $request->getAttribute('year')
                . '/0' . (int) $month . '/'
                . (string) $request->getAttribute('slug') . '/';

            return new RedirectResponse($uri->withPath($path), 301);
        }

        return $handler->handle($request);
    }
}
