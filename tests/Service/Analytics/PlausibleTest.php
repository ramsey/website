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
use PHPUnit\Framework\TestCase;
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
        $this->plausibleApi = Mockery::mock(PlausibleAPI::class);
        $this->service = new Plausible($this->plausibleApi, ['foo.example.com', 'bar.example.com']);
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
}
