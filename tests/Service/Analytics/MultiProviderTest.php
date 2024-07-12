<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\AnalyticsDetails;
use App\Service\Analytics\AnalyticsDetailsFactory;
use App\Service\Analytics\AnalyticsService;
use App\Service\Analytics\MultiProvider;
use App\Service\Analytics\UnknownAnalyticsDomain;
use Laminas\Diactoros\UriFactory;
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

    private AnalyticsDetails $analyticsDetails;

    protected function setUp(): void
    {
        $this->analyticsDetails = new AnalyticsDetails(
            eventName: 'anEvent',
            url: (new UriFactory())->createUri('https://example.com'),
        );
    }

    #[TestDox('calls recordEventFromDetails() on each service')]
    public function testMultiProvider(): void
    {
        $eventName = 'pageview';
        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();
        $tags = ['foo' => 'bar'];

        $factory = Mockery::mock(AnalyticsDetailsFactory::class);
        $factory
            ->expects('createFromWebContext')
            ->with($eventName, $request, $response, $tags)
            ->andReturn($this->analyticsDetails);

        $service1 = Mockery::mock(AnalyticsService::class);
        $service1->expects('recordEventFromDetails')->with($this->analyticsDetails);

        $service2 = Mockery::mock(AnalyticsService::class);
        $service2->expects('recordEventFromDetails')->with($this->analyticsDetails);

        $service3 = Mockery::mock(AnalyticsService::class);
        $service3->expects('recordEventFromDetails')->with($this->analyticsDetails);

        $multiProvider = new MultiProvider($factory, $service1, $service2, $service3);
        $multiProvider->recordEventFromWebContext($eventName, $request, $response, $tags);
    }

    #[TestDox('handles UnknownAnalyticsDomain exceptions')]
    public function testMultiProviderWithUnknownAnalyticsDomainException(): void
    {
        $eventName = 'pageview';
        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();
        $tags = ['foo' => 'bar'];

        $factory = Mockery::mock(AnalyticsDetailsFactory::class);
        $factory
            ->expects('createFromWebContext')
            ->with($eventName, $request, $response, $tags)
            ->andReturn($this->analyticsDetails);

        $service1 = Mockery::mock(AnalyticsService::class);
        $service1->expects('recordEventFromDetails')->with($this->analyticsDetails);

        $service2 = Mockery::mock(AnalyticsService::class);
        $service2
            ->expects('recordEventFromDetails')
            ->with($this->analyticsDetails)
            ->andThrow(new UnknownAnalyticsDomain());

        $service3 = Mockery::mock(AnalyticsService::class);
        $service3->expects('recordEventFromDetails')->with($this->analyticsDetails);

        $multiProvider = new MultiProvider($factory, $service1, $service2, $service3);
        $multiProvider->recordEventFromWebContext($eventName, $request, $response, $tags);
    }

    #[TestDox('does not handle other exceptions')]
    public function testMultiProviderWithOtherException(): void
    {
        $eventName = 'pageview';
        $request = Request::create('https://baz.example.com/path/to/page', 'GET');
        $response = new Response();
        $tags = ['foo' => 'bar'];

        $factory = Mockery::mock(AnalyticsDetailsFactory::class);
        $factory
            ->expects('createFromWebContext')
            ->with($eventName, $request, $response, $tags)
            ->andReturn($this->analyticsDetails);

        $service1 = Mockery::mock(AnalyticsService::class);
        $service1->expects('recordEventFromDetails')->with($this->analyticsDetails);

        $service2 = Mockery::mock(AnalyticsService::class);
        $service2
            ->expects('recordEventFromDetails')
            ->with($this->analyticsDetails)
            ->andThrow(new LogicException());

        $service3 = Mockery::mock(AnalyticsService::class);
        $service3->expects('recordEventFromDetails')->never();

        $multiProvider = new MultiProvider($factory, $service1, $service2, $service3);

        $this->expectException(LogicException::class);

        $multiProvider->recordEventFromWebContext($eventName, $request, $response, $tags);
    }
}
