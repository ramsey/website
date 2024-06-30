<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\Plausible;
use Devarts\PlausiblePHP\PlausibleAPI;
use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlausibleTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Generator $faker;
    private Plausible $service;
    private PlausibleAPI & MockInterface $plausibleApi;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $logger = Mockery::mock(LoggerInterface::class);
        $this->plausibleApi = Mockery::mock(PlausibleAPI::class);
        $this->plausibleApi->expects('setLogger')->with($logger);

        $this->service = new Plausible($this->plausibleApi, ['foo.example.com', 'bar.example.com'], $logger);
    }

    public function testRecordEventWhenDomainNotInList(): void
    {
        $this->plausibleApi->expects('recordEvent')->never();

        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();

        $this->service->recordEvent('pageview', $request, $response);
    }

    public function testRecordEventWithoutProperties(): void
    {
        $ip = $this->faker->ipv4();
        $referrer = $this->faker->url();

        $this->plausibleApi->expects('recordEvent')->with(
            'foo.example.com',
            'pageview',
            'https://foo.example.com/path/to/page',
            'MyUserAgent/1.0',
            $ip,
            $referrer,
            [
                'http_method' => 'GET',
                'http_referer' => $referrer,
                'status_code' => 200,
                'redirect_uri' => null,
            ],
            null,
        );

        $request = Request::create(
            uri: 'https://foo.example.com/path/to/page',
            method: 'GET',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/1.0',
                'HTTP_REFERER' => $referrer,
                'REMOTE_ADDR' => $ip,
            ],
        );

        $response = new Response();

        $this->service->recordEvent('pageview', $request, $response);
    }

    public function testRecordEventWithProperties(): void
    {
        $ip = $this->faker->ipv4();
        $currency = $this->faker->currencyCode();
        $redirectUri = $this->faker->url();

        $this->plausibleApi->expects('recordEvent')->with(
            'bar.example.com',
            'custom-event',
            'https://bar.example.com/path/to/page',
            'MyUserAgent/2.0',
            $ip,
            null,
            [
                'http_method' => 'POST',
                'http_referer' => null,
                'status_code' => 302,
                'redirect_uri' => $redirectUri,
                'extra_prop' => true,
            ],
            [
                'currency' => $currency,
                'amount' => 315.42,
            ],
        );

        $request = Request::create(
            uri: 'https://bar.example.com/path/to/page',
            method: 'POST',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/2.0',
                'REMOTE_ADDR' => $ip,
            ],
        );

        $response = new Response(status: 302, headers: ['location' => $redirectUri]);

        $this->service->recordEvent('custom-event', $request, $response, [
            'extra_prop' => true,
            'revenue' => [
                'currency' => $currency,
                'amount' => 315.42,
            ],
        ]);
    }

    public function testRecordEventUsingDigitalOceanConnectingIpHeader(): void
    {
        $ip = $this->faker->ipv4();
        $referrer = $this->faker->url();

        $this->plausibleApi->expects('recordEvent')->with(
            'foo.example.com',
            'pageview',
            'https://foo.example.com/path/to/page',
            'MyUserAgent/1.0',
            $ip,
            $referrer,
            [
                'http_method' => 'GET',
                'http_referer' => $referrer,
                'status_code' => 200,
                'redirect_uri' => null,
            ],
            null,
        );

        $request = Request::create(
            uri: 'https://foo.example.com/path/to/page',
            method: 'GET',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/1.0',
                'HTTP_REFERER' => $referrer,
                'HTTP_DO_CONNECTING_IP' => $ip,
                'REMOTE_ADDR' => 'should_not_be_accessed',
            ],
        );

        $response = new Response();

        $this->service->recordEvent('pageview', $request, $response);
    }

    #[TestWith(['https://archive/web/http%3A%2F%2Fexample.com/foo', 'https://archive/web/http://example.com/foo'])]
    #[TestWith(['https://archive/web/https%3A%2F%2Fexample.com/foo', 'https://archive/web/https://example.com/foo'])]
    #[TestWith(['http://archive/web/http%3A%2F%2Fexample.com/foo', 'http://archive/web/http://example.com/foo'])]
    #[TestWith(['http://archive/web/https%3A%2F%2Fexample.com/foo', 'http://archive/web/https://example.com/foo'])]
    public function testRecordEventWhenRedirectUriIsForArchiveDotOrg(
        string $expectedRedirectUri,
        string $redirectUri,
    ): void {
        $ip = $this->faker->ipv4();

        $this->plausibleApi->expects('recordEvent')->with(
            'foo.example.com',
            'pageview',
            'https://foo.example.com/path/to/page',
            'MyUserAgent/1.0',
            $ip,
            null,
            [
                'http_method' => 'GET',
                'http_referer' => null,
                'status_code' => 307,
                'redirect_uri' => $expectedRedirectUri,
            ],
            null,
        );

        $request = Request::create(
            uri: 'https://foo.example.com/path/to/page',
            method: 'GET',
            server: [
                'HTTP_USER_AGENT' => 'MyUserAgent/1.0',
                'REMOTE_ADDR' => $ip,
            ],
        );

        $response = new Response(
            status: 307,
            headers: ['location' => $redirectUri],
        );

        $this->service->recordEvent('pageview', $request, $response);
    }
}
