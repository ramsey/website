<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\Umami;
use App\Service\Analytics\UnknownAnalyticsDomain;
use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[TestDox('Umami analytics service')]
class UmamiTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Generator $faker;
    private HttpClientInterface & MockInterface $httpClient;
    private Umami $service;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->service = new Umami(
            'an_api_key',
            'https://umami.example.com/api/',
            [
                [
                    'domain' => 'foo.example.com',
                    'website_id' => 'foo_website_id',
                ],
                [
                    'domain' => 'bar.example.com',
                    'website_id' => 'bar_website_id',
                ],
            ],
            $this->httpClient,
        );
    }

    #[TestDox('throws UnknownAnalyticsDomain exception when domain is not in the list')]
    public function testRecordEventWhenDomainNotInList(): void
    {
        $this->httpClient->expects('request')->never();

        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();

        $this->expectException(UnknownAnalyticsDomain::class);
        $this->expectExceptionMessage('baz.example.com is not a valid analytics domain');

        $this->service->recordEvent('pageview', $request, $response);
    }

    #[TestDox('successfully records the event when no additional tags are provided')]
    public function testRecordEventWithoutTags(): void
    {
        $ip = $this->faker->ipv4();
        $referrer = $this->faker->url();

        $this->httpClient->expects('request')->with(
            'POST',
            'https://umami.example.com/api/send',
            Mockery::capture($data),
        );

        $request = Request::create(
            uri: 'https://bar.example.com/path/to/page',
            method: 'GET',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/1.0',
                'HTTP_REFERER' => $referrer,
                'REMOTE_ADDR' => $ip,
            ],
        );
        $request->setLocale('en-US');

        $response = new Response();

        $this->service->recordEvent('pageview', $request, $response);

        $this->assertSame(
            [
                'headers' => [
                    'user-agent' => 'MyUserAgent/1.0',
                    'x-forwarded-for' => $ip,
                    'x-umami-api-key' => 'an_api_key',
                ],
                'json' => [
                    'type' => 'event',
                    'payload' => [
                        'hostname' => 'bar.example.com',
                        'language' => 'en-US',
                        'referrer' => $referrer,
                        'url' => 'https://bar.example.com/path/to/page',
                        'website' => 'bar_website_id',
                        'name' => 'pageview',
                        'data' => [
                            'http_method' => 'GET',
                            'http_referer' => $referrer,
                            'status_code' => 200,
                            'redirect_uri' => null,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[TestDox('successfully records the event when additional tags are provided')]
    public function testRecordEventWithTags(): void
    {
        $ip = $this->faker->ipv4();
        $redirectUri = $this->faker->url();

        $this->httpClient->expects('request')->with(
            'POST',
            'https://umami.example.com/api/send',
            Mockery::capture($data),
        );

        $request = Request::create(
            uri: 'https://foo.example.com/path/to/page',
            method: 'POST',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/2.0',
                'REMOTE_ADDR' => $ip,
            ],
        );
        $request->setLocale('es-MX');

        $response = new Response(status: 302, headers: ['location' => $redirectUri]);

        $this->service->recordEvent('custom-event', $request, $response, [
            'extra_prop' => true,

            // This should override the http_referer value.
            'http_referer' => 'an_http_referer',
        ]);

        $this->assertSame(
            [
                'headers' => [
                    'user-agent' => 'MyUserAgent/2.0',
                    'x-forwarded-for' => $ip,
                    'x-umami-api-key' => 'an_api_key',
                ],
                'json' => [
                    'type' => 'event',
                    'payload' => [
                        'hostname' => 'foo.example.com',
                        'language' => 'es-MX',
                        'url' => 'https://foo.example.com/path/to/page',
                        'website' => 'foo_website_id',
                        'name' => 'pageview',
                        'data' => [
                            'http_method' => 'POST',
                            'http_referer' => 'an_http_referer',
                            'status_code' => 302,
                            'redirect_uri' => $redirectUri,
                            'extra_prop' => true,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[TestDox('uses do-connecting-ip header for IP address, if present')]
    public function testRecordEventUsingDigitalOceanConnectingIpHeader(): void
    {
        $doConnectingIp = $this->faker->ipv4();

        $this->httpClient->expects('request')->with(
            'POST',
            'https://umami.example.com/api/send',
            Mockery::capture($data),
        );

        $request = Request::create(
            uri: 'https://foo.example.com/path/to/page',
            method: 'GET',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/1.0',
                'HTTP_DO_CONNECTING_IP' => $doConnectingIp,
                'REMOTE_ADDR' => 'should_not_be_accessed',
            ],
        );
        $request->setLocale('en');

        $response = new Response();

        $this->service->recordEvent('pageview', $request, $response);

        $this->assertSame(
            [
                'headers' => [
                    'user-agent' => 'MyUserAgent/1.0',
                    'x-forwarded-for' => $doConnectingIp,
                    'x-umami-api-key' => 'an_api_key',
                ],
                'json' => [
                    'type' => 'event',
                    'payload' => [
                        'hostname' => 'foo.example.com',
                        'language' => 'en',
                        'url' => 'https://foo.example.com/path/to/page',
                        'website' => 'foo_website_id',
                        'name' => 'pageview',
                        'data' => [
                            'http_method' => 'GET',
                            'http_referer' => null,
                            'status_code' => 200,
                            'redirect_uri' => null,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[TestDox('escapes the :// in Archive.org redirect URLs to avoid problems in the analytics service')]
    #[TestWith(['https://archive/web/http%3A%2F%2Fexample.com/foo', 'https://archive/web/http://example.com/foo'])]
    #[TestWith(['https://archive/web/https%3A%2F%2Fexample.com/foo', 'https://archive/web/https://example.com/foo'])]
    #[TestWith(['http://archive/web/http%3A%2F%2Fexample.com/foo', 'http://archive/web/http://example.com/foo'])]
    #[TestWith(['http://archive/web/https%3A%2F%2Fexample.com/foo', 'http://archive/web/https://example.com/foo'])]
    public function testRecordEventWhenRedirectUriIsForArchiveDotOrg(
        string $expectedRedirectUri,
        string $redirectUri,
    ): void {
        $ip = $this->faker->ipv4();

        $this->httpClient->expects('request')->with(
            'POST',
            'https://umami.example.com/api/send',
            Mockery::capture($data),
        );

        $request = Request::create(
            uri: 'https://foo.example.com/path/to/page',
            method: 'GET',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/1.0',
                'REMOTE_ADDR' => $ip,
            ],
        );
        $request->setLocale('en-US');

        $response = new Response(
            status: 307,
            headers: ['location' => $redirectUri],
        );

        $this->service->recordEvent('pageview', $request, $response);

        $this->assertSame(
            [
                'headers' => [
                    'user-agent' => 'MyUserAgent/1.0',
                    'x-forwarded-for' => $ip,
                    'x-umami-api-key' => 'an_api_key',
                ],
                'json' => [
                    'type' => 'event',
                    'payload' => [
                        'hostname' => 'foo.example.com',
                        'language' => 'en-US',
                        'url' => 'https://foo.example.com/path/to/page',
                        'website' => 'foo_website_id',
                        'name' => 'pageview',
                        'data' => [
                            'http_method' => 'GET',
                            'http_referer' => null,
                            'status_code' => 307,
                            'redirect_uri' => $expectedRedirectUri,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }
}
