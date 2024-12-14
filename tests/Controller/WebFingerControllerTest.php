<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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

    #[TestDox('Request to /.well-known/webfinger responds with 400')]
    public function testWebFingerRespondsWithBadRequest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/webfinger');

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'application/jrd+json; charset=utf-8');
    }

    #[TestDox('Request to /.well-known/webfinger?resource=acct%3AFrodo%40example.com responds with 404')]
    public function testWebFingerRespondsWithNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/webfinger?resource=acct%3AFrodo%40example.com');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'application/jrd+json; charset=utf-8');
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
    #[TestWith(['localhost', 'acct:ben@benramsey.dev'])]
    #[TestWith(['localhost', 'acct:invalid@benramsey.dev', false])]
    public function testWebFingerResponsesForAccountRequests(
        string $host,
        string $resourceValue,
        bool $shouldPass = true,
    ): void {
        $client = static::createClient();
        $client->request('GET', "https://{$host}/.well-known/webfinger?resource=" . urlencode($resourceValue));

        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'application/jrd+json; charset=utf-8');

        /** @var Response $response */
        $response = $client->getResponse();

        if ($shouldPass) {
            $this->assertResponseIsSuccessful();
            $this->assertJsonStringEqualsJsonString(
                (string) json_encode(self::EXPECTED_DATA),
                (string) $response->getContent(),
            );
        } else {
            $this->assertResponseStatusCodeSame(404);
            $this->assertJsonStringEqualsJsonString('{}', (string) $response->getContent());
        }
    }

    #[TestDox('Request to /.well-known/webfinger with limited relations')]
    public function testWebFingerResponsesWithLimitedRelations(): void
    {
        $expectedData = self::EXPECTED_DATA;
        $expectedLinks = $expectedData['links'];

        // Remove link with rel=self
        unset($expectedLinks[3]);

        // Remove link with rel=http://webfinger.net/rel/avatar
        unset($expectedLinks[1]);

        // Reset the array keys.
        $expectedData['links'] = array_values($expectedLinks);

        $client = static::createClient();
        $client->request(
            'GET',
            'https://ramsey.dev/.well-known/webfinger?resource=' . urlencode('acct:ben@ramsey.dev')
            . '&rel=me&rel=' . urlencode('http://webfinger.net/rel/profile-page'),
        );

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($expectedData),
            (string) $response->getContent(),
        );
    }
}
