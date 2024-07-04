<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\AnalyticsService;
use App\Service\Analytics\MultiProvider;
use App\Service\Analytics\UnknownAnalyticsDomain;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('MultiProvider analytics service')]
class MultiProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('calls recordEvent() on each service')]
    public function testMultiProvider(): void
    {
        $eventName = 'pageview';
        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();
        $tags = ['foo' => 'bar'];

        $service1 = Mockery::mock(AnalyticsService::class);
        $service1->expects('recordEvent')->with($eventName, $request, $response, $tags);

        $service2 = Mockery::mock(AnalyticsService::class);
        $service2->expects('recordEvent')->with($eventName, $request, $response, $tags);

        $service3 = Mockery::mock(AnalyticsService::class);
        $service3->expects('recordEvent')->with($eventName, $request, $response, $tags);

        $multiProvider = new MultiProvider($service1, $service2, $service3);
        $multiProvider->recordEvent($eventName, $request, $response, $tags);
    }

    #[TestDox('handles UnknownAnalyticsDomain exceptions')]
    public function testMultiProviderWithUnknownAnalyticsDomainException(): void
    {
        $eventName = 'pageview';
        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();
        $tags = ['foo' => 'bar'];

        $service1 = Mockery::mock(AnalyticsService::class);
        $service1->expects('recordEvent')->with($eventName, $request, $response, $tags);

        $service2 = Mockery::mock(AnalyticsService::class);
        $service2
            ->expects('recordEvent')
            ->with($eventName, $request, $response, $tags)
            ->andThrow(new UnknownAnalyticsDomain());

        $service3 = Mockery::mock(AnalyticsService::class);
        $service3->expects('recordEvent')->with($eventName, $request, $response, $tags);

        $multiProvider = new MultiProvider($service1, $service2, $service3);
        $multiProvider->recordEvent($eventName, $request, $response, $tags);
    }

    #[TestDox('does not handle other exceptions')]
    public function testMultiProviderWithOtherException(): void
    {
        $eventName = 'pageview';
        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();
        $tags = ['foo' => 'bar'];

        $service1 = Mockery::mock(AnalyticsService::class);
        $service1->expects('recordEvent')->with($eventName, $request, $response, $tags);

        $service2 = Mockery::mock(AnalyticsService::class);
        $service2
            ->expects('recordEvent')
            ->with($eventName, $request, $response, $tags)
            ->andThrow(new LogicException());

        $service3 = Mockery::mock(AnalyticsService::class);
        $service3->expects('recordEvent')->never();

        $multiProvider = new MultiProvider($service1, $service2, $service3);

        $this->expectException(LogicException::class);

        $multiProvider->recordEvent($eventName, $request, $response, $tags);
    }
}
