<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RFC 7033: WebFinger, https://datatracker.ietf.org/doc/html/rfc7033
 */
final class WebFingerController extends AbstractController
{
    private const CONTENT_TYPE = 'application/jrd+json';

    private const BEN_RAMSEY_DEV = [
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

    private const RESOURCES = [
        '127.0.0.1' => [
            'acct:ben@ramsey.dev' => self::BEN_RAMSEY_DEV,
        ],
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

    #[Route('/.well-known/webfinger')]
    public function handle(Request $request): Response
    {
        $headers = ['content-type' => self::CONTENT_TYPE];

        $resource = $request->query->getString('resource');

        if (trim($resource) === '') {
            return new Response('{}', Response::HTTP_BAD_REQUEST, $headers);
        }

        $requestedRelations = $this->parseRelations($request);

        $resources = $this->getResourcesForDomain($request->getHost());
        if (array_key_exists($resource, $resources)) {
            $data = $resources[$resource];

            $links = [];
            foreach ($data['links'] ?? [] as $link) {
                if (count($requestedRelations) > 0 && !in_array($link['rel'], $requestedRelations)) {
                    // Skip this link if the relation wasn't requested.
                    continue;
                }

                // If the href begins with a "/", turn it into an absolute URL.
                if (isset($link['href']) && str_starts_with($link['href'], '/')) {
                    $link['href'] = $request->getUriForPath($link['href']);
                }

                $links[] = $link;
            }

            $data['links'] = $links;

            return new JsonResponse(data: $data, headers: $headers);
        }

        return new Response('{}', Response::HTTP_NOT_FOUND, $headers);
    }

    private function parseRelations(Request $request): array
    {
        $parts = array_map(
            function (string $value): array {
                [$k, $v] = explode('=', $value, 2);

                return ['key' => urldecode($k), 'value' => urldecode($v)];
            },
            explode('&', $request->server->get('QUERY_STRING')),
        );

        $relations = [];
        foreach ($parts as $part) {
            if (strtolower($part['key']) === 'rel') {
                $relations[] = $part['value'];
            }
        }

        return $relations;
    }

    private function getResourcesForDomain(string $domain): array
    {
        return self::RESOURCES[$domain] ?? [];
    }
}
