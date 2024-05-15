<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\CacheTtl;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

use function array_key_exists;
use function array_map;
use function count;
use function explode;
use function in_array;
use function md5;
use function str_starts_with;
use function strtolower;
use function trim;
use function urldecode;

/**
 * Implements the WebFinger protocol
 *
 * @link https://www.rfc-editor.org/rfc/rfc7033.html RFC 7033: WebFinger
 *
 * @phpstan-type WebLink array{
 *     rel: string,
 *     href: string,
 *     type: string,
 * }
 * @phpstan-type WebFingerResource array{
 *     aliases: list<string>,
 *     links: list<WebLink>,
 *     properties: array{
 *         "https://schema.org/name": string,
 *         "https://schema.org/email": string,
 *     },
 *     subject: string,
 * }
 */
#[AsController]
#[Route('/.well-known/webfinger')]
#[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
final readonly class WebFingerController
{
    private const array HEADERS = [
        'access-control-allow-origin' => '*',
        'content-type' => 'application/jrd+json',
    ];

    /**
     * @phpstan-var WebFingerResource
     * @noinspection HttpUrlsUsage
     */
    private const array BEN_RAMSEY_DEV = [
        'aliases' => [],
        'links' => [
            [
                'rel' => 'me',
                'href' => 'https://ben.ramsey.dev',
                'type' => 'text/html',
            ],
            [
                'rel' => 'http://webfinger.net/rel/avatar',
                'href' => 'https://www.gravatar.com/avatar/a0fa77843de8a4a2265bb939180a384b.jpg?s=2000',
                'type' => 'image/png',
            ],
            [
                'rel' => 'http://webfinger.net/rel/profile-page',
                'href' => 'https://ben.ramsey.dev',
                'type' => 'text/html',
            ],
            [
                'rel' => 'self',
                'href' => 'https://phpc.social/users/ramsey',
                'type' => 'application/activity+json',
            ],
        ],
        'properties' => [
            'https://schema.org/name' => 'Ben Ramsey',
            'https://schema.org/email' => 'ben@ramsey.dev',
        ],
        'subject' => 'acct:ben@ramsey.dev',
    ];

    /**
     * @phpstan-var array<string, array<string, WebFingerResource>>
     */
    private const array RESOURCES = [
        'ramsey.dev' => [
            'acct:ben@ramsey.dev' => self::BEN_RAMSEY_DEV,
        ],
        'benramsey.com' => [
            'acct:ben@benramsey.com' => self::BEN_RAMSEY_DEV,
        ],
        'benramsey.dev' => [
            'acct:ben@benramsey.dev' => self::BEN_RAMSEY_DEV,
        ],
    ];

    public function __invoke(Request $request): Response
    {
        $resource = $request->query->getString('resource');

        if (trim($resource) === '') {
            return new Response('{}', Response::HTTP_BAD_REQUEST, self::HEADERS);
        }

        $requestedRelations = $this->parseRelations($request);

        $resources = $this->getResourcesForDomain($request->getHost());
        if (array_key_exists($resource, $resources)) {
            $data = $resources[$resource];

            $links = [];
            foreach ($data['links'] as $link) {
                if (count($requestedRelations) > 0 && !in_array($link['rel'], $requestedRelations)) {
                    // Skip this link if the relation wasn't requested.
                    continue;
                }

                // If the href begins with a "/", turn it into an absolute URL.
                if (str_starts_with($link['href'], '/')) {
                    $link['href'] = $request->getUriForPath($link['href']);
                }

                $links[] = $link;
            }

            $data['links'] = $links;

            $response = new JsonResponse(data: $data, headers: self::HEADERS);
            $response->setEtag(md5((string) $response->getContent()));
            $response->isNotModified($request);

            return $response;
        }

        return new Response('{}', Response::HTTP_NOT_FOUND, self::HEADERS);
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

    /**
     * @phpstan-return array<string, WebFingerResource>
     */
    private function getResourcesForDomain(string $domain): array
    {
        return self::RESOURCES[$domain] ?? [];
    }
}
