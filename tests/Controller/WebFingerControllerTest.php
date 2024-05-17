<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\WebFingerController;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function array_values;
use function json_encode;
use function urlencode;

class WebFingerControllerTest extends WebTestCase
{
    /**
     * @noinspection HttpUrlsUsage
     */
    private const array EXPECTED_DATA = [
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

    #[TestDox('Request to /.well-known/webfinger returns Bad Request response')]
    public function testWebFingerRespondsWithBadRequest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/webfinger');

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'application/jrd+json');
    }

    #[TestDox('Request to /.well-known/webfinger?resource=acct%3AFrodo%40example.com returns Not Found response')]
    public function testWebFingerRespondsWithNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/webfinger?resource=acct%3AFrodo%40example.com');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'application/jrd+json');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to /.well-known/webfinger')]
    #[TestWith(['ramsey.dev', 'acct:ben@ramsey.dev'])]
    #[TestWith(['ramsey.dev', 'acct:invalid@ramsey.dev', false])]
    #[TestWith(['benramsey.com', 'acct:ben@benramsey.com'])]
    #[TestWith(['benramsey.com', 'acct:invalid@benramsey.com', false])]
    #[TestWith(['benramsey.dev', 'acct:ben@benramsey.dev'])]
    #[TestWith(['benramsey.dev', 'acct:invalid@benramsey.dev', false])]
    public function testWebFingerResponsesForAccountRequests(
        string $host,
        string $resourceValue,
        bool $shouldPass = true,
    ): void {
        $request = Request::create(
            "https://{$host}/.well-known/webfinger?resource=" . urlencode($resourceValue),
        );

        $controller = new WebFingerController();
        $response = $controller($request);

        $this->assertSame('*', $response->headers->get('access-control-allow-origin'));
        $this->assertSame('application/jrd+json', $response->headers->get('Content-Type'));

        if ($shouldPass) {
            $this->assertSame(200, $response->getStatusCode());
            $this->assertJsonStringEqualsJsonString(
                (string) json_encode(self::EXPECTED_DATA),
                (string) $response->getContent(),
            );
        } else {
            $this->assertSame(404, $response->getStatusCode());
            $this->assertJsonStringEqualsJsonString('{}', (string) $response->getContent());
        }
    }

    #[TestDox('Request to /.well-known/webfinger with limited relations')]
    public function testWebFingerResponsesWithLimitedRelations(): void
    {
        $request = Request::create(
            'https://ramsey.dev/.well-known/webfinger?resource='
            . urlencode('acct:ben@ramsey.dev')
            . '&rel=me&rel=' . urlencode('http://webfinger.net/rel/profile-page'),
        );

        $expectedData = self::EXPECTED_DATA;
        $expectedLinks = $expectedData['links'];

        // Remove link with rel=self
        unset($expectedLinks[3]);

        // Remove link with rel=http://webfinger.net/rel/avatar
        unset($expectedLinks[1]);

        // Reset the array keys.
        $expectedData['links'] = array_values($expectedLinks);

        $controller = new WebFingerController();
        $response = $controller($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($expectedData),
            (string) $response->getContent(),
        );
    }
}
