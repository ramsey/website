<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\AnalyticsDetails;
use App\Service\Analytics\AnalyticsDetailsFactory;
use App\Service\Analytics\Umami;
use App\Service\Analytics\UnknownAnalyticsDomain;
use Faker\Factory;
use Faker\Generator;
use Laminas\Diactoros\UriFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[TestDox('Umami analytics service')]
class UmamiTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AnalyticsDetails $analyticsDetails;
    private AnalyticsDetailsFactory & MockInterface $analyticsDetailsFactory;
    private Generator $faker;
    private HttpClientInterface & MockInterface $httpClient;
    private Umami $service;
    private TestHandler $testHandler;
    private UriInterface & MockInterface $uri;

    protected function setUp(): void
    {
        $this->analyticsDetailsFactory = Mockery::mock(AnalyticsDetailsFactory::class);
        $this->faker = Factory::create();
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->uri = Mockery::mock(UriInterface::class);

        $this->testHandler = new TestHandler();
        $logger = new Logger('test', [$this->testHandler]);

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
            $this->analyticsDetailsFactory,
            $logger,
        );

        $this->analyticsDetails = new AnalyticsDetails(
            eventName: 'anEvent',
            url: $this->uri,
        );
    }

    #[TestDox('recordEventFromWebContext() throws UnknownAnalyticsDomain exception when domain is not in the list')]
    public function testRecordEventWhenDomainNotInList(): void
    {
        $this->httpClient->expects('request')->never();
        $this->uri->allows('getPath')->andReturn('/path/to/content');
        $this->uri->allows('getHost')->andReturn('baz.example.com');

        $this->expectException(UnknownAnalyticsDomain::class);
        $this->expectExceptionMessage('baz.example.com is not a valid analytics domain');

        $this->service->recordEventFromDetails($this->analyticsDetails);
    }

    #[TestDox('recordEventFromWebContext() throws UnknownAnalyticsDomain exception when domain is not in the list')]
    public function testRecordEventFromWebContextWhenDomainNotInList(): void
    {
        $this->httpClient->expects('request')->never();
        $this->uri->allows('getPath')->andReturn('/path/to/content');
        $this->uri->allows('getHost')->andReturn('qux.example.com');

        $request = Request::create('https://baz.example.com');
        $response = new Response();

        $this->analyticsDetailsFactory
            ->expects('createFromWebContext')
            ->with('event', $request, $response, [])
            ->andReturn($this->analyticsDetails);

        $this->expectException(UnknownAnalyticsDomain::class);
        $this->expectExceptionMessage('qux.example.com is not a valid analytics domain');

        $this->service->recordEventFromWebContext('event', $request, $response, []);
    }

    #[TestDox('recordEventFromDetails() successfully records the event')]
    public function testFromDetails(): void
    {
        $url = 'https://bar.example.com/path/to/page';
        $ip = $this->faker->ipv4();
        $referrer = $this->faker->url();
        $userAgent = $this->faker->userAgent();

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'pageview',
            url: (new UriFactory())->createUri($url),
            ipAddress: $ip,
            locale: 'en-US',
            referrer: (new UriFactory())->createUri($referrer),
            tags: ['http_method' => 'GET', 'status_code' => 200],
            userAgent: $userAgent,
        );

        $this->httpClient->expects('request')->with(
            'POST',
            'https://umami.example.com/api/send',
            Mockery::capture($data),
        );

        $this->service->recordEventFromDetails($analyticsDetails);

        $this->assertSame(
            [
                'headers' => [
                    'user-agent' => $userAgent,
                    'x-forwarded-for' => $ip,
                    'x-umami-api-key' => 'an_api_key',
                ],
                'json' => [
                    'type' => 'event',
                    'payload' => [
                        'hostname' => 'bar.example.com',
                        'language' => 'en-US',
                        'referrer' => $referrer,
                        'url' => $url,
                        'website' => 'bar_website_id',
                        'name' => 'pageview',
                        'data' => [
                            'http_method' => 'GET',
                            'status_code' => 200,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[TestDox('recordEventFromDetails() skips path')]
    #[TestWith(['/health/'])]
    public function testFromDetailsSkipsPath(string $path): void
    {
        $url = 'https://foo.example.com' . $path;

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'pageview',
            url: (new UriFactory())->createUri($url),
        );

        $this->httpClient->expects('request')->never();

        $this->service->recordEventFromDetails($analyticsDetails);
    }

    #[TestDox('recordEventFromWebContext() successfully records the event')]
    public function testFromWebContext(): void
    {
        $url = 'https://foo.example.com/path/to/page';
        $ip = $this->faker->ipv4();
        $redirectUri = $this->faker->url();
        $userAgent = $this->faker->userAgent();

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'custom-event',
            url: (new UriFactory())->createUri($url),
            ipAddress: $ip,
            locale: 'en-US',
            redirectUrl: (new UriFactory())->createUri($redirectUri),
            tags: ['http_method' => 'POST', 'status_code' => 302, 'extra_prop' => true],
            userAgent: $userAgent,
        );

        $this->httpClient->expects('request')->with(
            'POST',
            'https://umami.example.com/api/send',
            Mockery::capture($data),
        );

        $request = Request::create($url);
        $response = new Response();

        $this->analyticsDetailsFactory
            ->expects('createFromWebContext')
            ->with('custom-event', $request, $response, ['extra_prop' => true])
            ->andReturn($analyticsDetails);

        $this->service->recordEventFromWebContext('custom-event', $request, $response, ['extra_prop' => true]);

        $this->assertSame(
            [
                'headers' => [
                    'user-agent' => $userAgent,
                    'x-forwarded-for' => $ip,
                    'x-umami-api-key' => 'an_api_key',
                ],
                'json' => [
                    'type' => 'event',
                    'payload' => [
                        'hostname' => 'foo.example.com',
                        'language' => 'en-US',
                        'url' => $url,
                        'website' => 'foo_website_id',
                        'name' => 'pageview',
                        'data' => [
                            'http_method' => 'POST',
                            'status_code' => 302,
                            'extra_prop' => true,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[TestDox('recordEventFromWebContext() skips path')]
    #[TestWith(['/health/foo'])]
    public function testFromWebContextSkipsPath(string $path): void
    {
        $url = 'https://bar.example.com' . $path;

        $this->httpClient->expects('request')->never();
        $this->analyticsDetailsFactory->expects('createFromWebContext')->never();

        $request = Request::create($url);
        $response = new Response();

        $this->service->recordEventFromWebContext('custom-event', $request, $response, []);
    }

    #[TestDox('logs an error when failing to send data to Umami')]
    public function testLogsFailureToRecord(): void
    {
        $url = 'https://foo.example.com/path/to/page';
        $ip = $this->faker->ipv4();
        $userAgent = $this->faker->userAgent();

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'pageview',
            url: (new UriFactory())->createUri($url),
            ipAddress: $ip,
            userAgent: $userAgent,
        );

        $this->httpClient->expects('request')->with(
            'POST',
            'https://umami.example.com/api/send',
            Mockery::capture($data),
        )->andThrow(new class extends RuntimeException implements TransportExceptionInterface {
        });

        $this->service->recordEventFromDetails($analyticsDetails);

        $this->assertTrue($this->testHandler->hasErrorThatContains('Unable to send analytics to Umami:'));
    }
}
