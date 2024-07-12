<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\AnalyticsDetails;
use App\Service\Analytics\AnalyticsDetailsFactory;
use App\Service\Analytics\Plausible;
use App\Service\Analytics\UnknownAnalyticsDomain;
use Devarts\PlausiblePHP\PlausibleAPI;
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
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Plausible analytics service')]
class PlausibleTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AnalyticsDetails $analyticsDetails;
    private AnalyticsDetailsFactory & MockInterface $analyticsDetailsFactory;
    private Generator $faker;
    private PlausibleAPI & MockInterface $plausibleApi;
    private Plausible $service;
    private TestHandler $testHandler;
    private UriInterface & MockInterface $uri;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->plausibleApi = Mockery::mock(PlausibleAPI::class);
        $this->analyticsDetailsFactory = Mockery::mock(AnalyticsDetailsFactory::class);
        $this->uri = Mockery::mock(UriInterface::class);

        $this->testHandler = new TestHandler();
        $logger = new Logger('test', [$this->testHandler]);

        $this->service = new Plausible(
            $this->plausibleApi,
            ['foo.example.com', 'bar.example.com'],
            $this->analyticsDetailsFactory,
            $logger,
        );

        $this->analyticsDetails = new AnalyticsDetails(
            eventName: 'anEvent',
            url: $this->uri,
        );
    }

    #[TestDox('recordEventFromDetails() throws UnknownAnalyticsDomain exception when domain is not in the list')]
    public function testRecordEventFromDetailsWhenDomainNotInList(): void
    {
        $this->plausibleApi->expects('recordEvent')->never();
        $this->uri->allows('getPath')->andReturn('/path/to/content');
        $this->uri->allows('getHost')->andReturn('baz.example.com');

        $this->expectException(UnknownAnalyticsDomain::class);
        $this->expectExceptionMessage('baz.example.com is not a valid analytics domain');

        $this->service->recordEventFromDetails($this->analyticsDetails);
    }

    #[TestDox('recordEventFromWebContext() throws UnknownAnalyticsDomain exception when domain is not in the list')]
    public function testRecordEventFromWebContextWhenDomainNotInList(): void
    {
        $this->plausibleApi->expects('recordEvent')->never();
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
        $url = 'https://foo.example.com/path/to/page';
        $ip = $this->faker->ipv4();
        $referrer = $this->faker->url();
        $userAgent = $this->faker->userAgent();

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'pageview',
            url: (new UriFactory())->createUri($url),
            ipAddress: $ip,
            referrer: (new UriFactory())->createUri($referrer),
            tags: ['http_method' => 'GET', 'status_code' => 200],
            userAgent: $userAgent,
        );

        $this->plausibleApi->expects('recordEvent')->with(
            'foo.example.com',
            'pageview',
            $url,
            $userAgent,
            $ip,
            $referrer,
            [
                'http_referer' => $referrer,
                'redirect_uri' => null,
                'http_method' => 'GET',
                'status_code' => 200,
            ],
        );

        $this->service->recordEventFromDetails($analyticsDetails);
    }

    #[TestDox('recordEventFromDetails() skips path')]
    #[TestWith(['/health?foo=bar'])]
    public function testFromDetailsSkipsPath(string $path): void
    {
        $url = 'https://foo.example.com' . $path;

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'pageview',
            url: (new UriFactory())->createUri($url),
        );

        $this->plausibleApi->expects('recordEvent')->never();

        $this->service->recordEventFromDetails($analyticsDetails);
    }

    #[TestDox('recordEventFromWebContext() successfully records the event')]
    public function testFromWebContext(): void
    {
        $url = 'https://bar.example.com/path/to/page';
        $ip = $this->faker->ipv4();
        $redirectUri = $this->faker->url();
        $userAgent = $this->faker->userAgent();

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'custom-event',
            url: (new UriFactory())->createUri($url),
            ipAddress: $ip,
            redirectUrl: (new UriFactory())->createUri($redirectUri),
            tags: ['http_method' => 'POST', 'status_code' => 302, 'extra_prop' => true],
            userAgent: $userAgent,
        );

        $this->plausibleApi->expects('recordEvent')->with(
            'bar.example.com',
            'custom-event',
            $url,
            $userAgent,
            $ip,
            null,
            [
                'http_referer' => null,
                'redirect_uri' => $redirectUri,
                'http_method' => 'POST',
                'status_code' => 302,
                'extra_prop' => true,
            ],
        );

        $request = Request::create($url);
        $response = new Response();

        $this->analyticsDetailsFactory
            ->expects('createFromWebContext')
            ->with('custom-event', $request, $response, ['extra_prop' => true])
            ->andReturn($analyticsDetails);

        $this->service->recordEventFromWebContext('custom-event', $request, $response, ['extra_prop' => true]);
    }

    #[TestDox('recordEventFromWebContext() skips path')]
    #[TestWith(['/health'])]
    public function testFromWebContextSkipsPath(string $path): void
    {
        $url = 'https://bar.example.com' . $path;

        $this->plausibleApi->expects('recordEvent')->never();
        $this->analyticsDetailsFactory->expects('createFromWebContext')->never();

        $request = Request::create($url);
        $response = new Response();

        $this->service->recordEventFromWebContext('custom-event', $request, $response, []);
    }

    #[TestDox('logs an error when failing to send data to Plausible')]
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

        $this->plausibleApi->expects('recordEvent')->with(
            'foo.example.com',
            'pageview',
            $url,
            $userAgent,
            $ip,
            null,
            [
                'http_referer' => null,
                'redirect_uri' => null,
            ],
        )->andThrow(new class extends RuntimeException implements ClientExceptionInterface {
        });

        $this->service->recordEventFromDetails($analyticsDetails);

        $this->assertTrue($this->testHandler->hasErrorThatContains('Unable to send analytics to Plausible:'));
    }
}
